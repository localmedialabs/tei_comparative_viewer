<?php

namespace App\Http\Services\Viewer;

use Illuminate\Http\UploadedFile;
use DiDom\Document;
use App\Libraries\ParseXMLString;

/**
 * TEIの表示データに関する処理を行う
 */
class ViewerService
{
  /**
   * TEIの表示の対象データを返却する
   *
   * @param UploadedFile $tei
   * @param string $tei_url
   * @return array{
   *     manifest_data: array,
   *     tei_url: string,
   *     bibliography: array,
   *     zones: array,
   *     is_one_page: bool,
   *     text_info: array,
   *     outputs: array,
   *     people: array,
   *     places: array,
   *     coordinate: array,
   *     audio: UploadedFile|null
   * }
   */
  public function __invoke(
    UploadedFile $tei,
    string $tei_url
  ): array {
    $xml = simplexml_load_file($tei);

    $manifest_data = [];
    $manifest_url = '';

    if (isset($xml->facsimile['sameAs'])) {
      // IIIFマニフェストを取得しjsonを連想配列に変換
      $manifest_url = (string)$xml->facsimile['sameAs'];
      try {
        $jsonData = file_get_contents($manifest_url);
      } catch (\Exception $e) {
        // file_get_contents()がセキュリティの関係で使用できない場合、curlを使用
        $curl_p = curl_init();
        curl_setopt($curl_p, CURLOPT_URL, $manifest_url);
        curl_setopt($curl_p, CURLOPT_RETURNTRANSFER, true);
        $jsonData = curl_exec($curl_p);
        curl_close($curl_p);
      }

      $data = json_decode($jsonData, true);

      // IIIF情報取得
      if (isset($data)) {
        foreach ($data["items"] as $item) {
          $body = $item['items'][0]['items'][0]['body'] ?? null;
          $serviceId = $body['service'][0]['id'] ?? '';

          $manifest_data[] = [
            'id' => $item['id'],
            'info' => $serviceId . '/info.json',
            'width' => $item['width'],
            'height' => $item['height'],
          ];
        }
      } else {
        // マニフェストが取得できない場合はダミーデータを追加
        $manifest_data[] = [
          'id' => 0,
          'info' => '',
          'width' => 0,
          'height' => 0,
        ];
      }
    }

    // TeiHeader処理
    // bibliographyデータの取得
    $teiHeader = $xml->teiHeader;
    $fileDesc = $teiHeader->fileDesc;
    $publication = $fileDesc->publicationStmt;
    $sourceDesc = $fileDesc->sourceDesc;
    $bibliography = [
      'names' => getRespNameMap($fileDesc->titleStmt->respStmt), // キー：役割、値：担当者氏名
      'publisher' => getElementText($publication->publisher), // TEIテキストの出版社
      'publish_date' => getElementText($publication->date), // 公開年月日
      'licence' => getElementText($publication->availability->licence), // ライセンス名称
      'licence_url' => getAttribute($publication->availability->licence, 'target'), // ライセンス情報URL
      'notes' => getArray($fileDesc->notesStmt->note), // 注記
      'title' => getElementText($sourceDesc->bibl->title), // タイトル
      'authors' => getArray($sourceDesc->bibl->author), // 著者
      'editors' => getArray($sourceDesc->bibl->editor), // 編者/版元
      'date' => getElementText($sourceDesc->bibl->date), // 出版年/製作年（年代）
      'biblScope' => getElementText($sourceDesc->bibl->biblScope) // 員数（典拠参照の範囲）
    ];
    if ($bibliography['title'] === "") {
      $bibliography['title'] = getElementText($fileDesc->titleStmt->title);
    }
    if ($sourceDesc->msDesc) {
      $msDesc = $sourceDesc->msDesc;
      $bibliography['institution'] = getElementText($msDesc->msIdentifier->institution); // 所蔵者
      $bibliography['repository'] = getAttribute($msDesc->msIdentifier->repository, 'ref'); // 所蔵
      $bibliography['collections'] = getArray($msDesc->msIdentifier->collection); // コレクション名
      $bibliography['idno'] = getElementText($msDesc->msIdentifier->idno); // ハンドルURL
      $bibliography['idno_type'] = getAttribute($msDesc->msIdentifier->idno, 'type'); // idnoのtype属性の値取得
      $bibliography['summary'] =  getElementText($msDesc->msContents->summary); // 資料の概要、要約
      if ($msDesc->msContents) {
        $msContents = $msDesc->msContents;
        $bibliography['ms_titles'] = getAttributeTextMap($msContents->msItem->title, 'type'); // 外題/表題/内題/首題・版心題/通用名称/各分野の専門的名称（cover：外題/表題、caption：内題、alt：首題・版心題/通用名称/各分野の専門的名称）
        $bibliography['colophon'] = getElementText($msContents->msItem->colophon); // 奥書
      }
      if ($msDesc->physDesc) {
        $physDesc = $msDesc->physDesc;
        if ($physDesc->objectDesc) {
          $objectDesc = $physDesc->objectDesc;
          $bibliography['support'] = getElementText($objectDesc->supportDesc->support); // 料紙
          $bibliography['extent'] = getElementText($objectDesc->supportDesc->extent); // 丁数/紙数
          if ($objectDesc->supportDesc) {
            $supportDesc = $objectDesc->supportDesc;
            $bibliography['unit'] = getAttribute($supportDesc->extent->dimensions, 'unit'); // dimensionsの単位取得
            if ($supportDesc->extent) {
              $extent = $supportDesc->extent;
              $bibliography['height'] = getElementText($extent->dimensions->height); // 縦
              $bibliography['width'] = getElementText($extent->dimensions->width); // 横
              $bibliography['depth'] = getElementText($extent->dimensions->depth); // 高さ
            }
          }
        }
        $bibliography['decoNote'] = getElementText($physDesc->decoDesc->decoNote); // 付属品情報
        $bibliography['abs'] = getArray($physDesc->additions->ab); // 識語
        $bibliography['bindingDesc'] = getElementText($physDesc->bindingDesc->binding->p); // 形態/品質・形状
        $bibliography['sealDescs'] = getArray($physDesc->sealDesc->p); // 蔵書印など
      }
      if ($msDesc->history) {
        $history = $msDesc->history;
        $bibliography['origDate'] = getElementText($history->origin->origDate); // 完成年
        $bibliography['provenances'] = getArray($history->provenance->p); // 入手経緯のエピソード
        $bibliography['acquisitions'] = getArrayFullText($history->acquisition->p, 'name'); // 旧蔵者/伝来過程
      }
    }
    if (!array_key_exists('ms_titles', $bibliography)) { // ms_titlesが存在しない場合のエラー回避
      $bibliography['ms_titles'] = [];
      $bibliography['ms_titles']['cover'] = '';
      $bibliography['ms_titles']['caption'] = '';
      $bibliography['ms_titles']['alt'] = '';
    }
    if ($xml->teiHeader->profileDesc) {
      $profileDesc = $xml->teiHeader->profileDesc;
      if ($profileDesc->correspDesc && $profileDesc->correspDesc->correspAction) {
        foreach ($profileDesc->correspDesc->correspAction as $action) {
          // type属性の値を取得
          $type = (string)($action['type'] ?? null);
          if ($type === "sent") {
            $bibliography['sentPersNames'] = getArray($action->persName); // 差出人/作成者
            $bibliography['sentDate'] = getElementText($action->date); // 送信年月日
          } elseif ($type === "received") {
            $bibliography['receivedPersNames'] = getArray($action->persName); // 名宛人/受取人
            $bibliography['receivedDate'] = getElementText($action->date); // 受信年月日
          }
        }
      }
      if ($profileDesc->textClass) {
        $textClass = $profileDesc->textClass;
        $bibliography['terms'] = getArray($textClass->keywords->term); // 主題
      }
    }

    // facsimile処理
    // zones取得
    $zones = []; // propsに送る配列定義
    foreach ($xml->facsimile->surface as $value) {
      foreach ($value->zone as $zone) {
        $zoneId = $zone->attributes('xml', true)['id'] ?? null; // 属性に「xml:id」があるかチェック
        if ($zone && $zoneId) {
          // データを追加
          $zones[(string)$zoneId] = [
            'label' => (string)($value->label ?? ""),
            'ulx'   => (string)($zone['ulx'] ?? "0"),
            'uly'   => (string)($zone['uly'] ?? "0"),
            'lrx'   => (string)($zone['lrx'] ?? "0"),
            'lry'   => (string)($zone['lry'] ?? "0"),
          ];
        }
      }
    }

    // 1ページか複数ページか
    $is_one_page = count($zones) === 1 ? true : false;

    // text処理
    // body処理
    $text_info['writeMode'] = (string)$xml->text->body['style'] === 'writing-mode: vertical-rl;' || (string)$xml->text->body['style'] === '' ? 'vertical' : 'horizontal'; // 記述方向取得（基本縦書きで記述がない場合もある）
    $text_info['lang'] = $xml->text->body->attributes('xml', true)['lang'] ?? null; // 言語情報取得

    // テキスト内の人名、地名情報収集
    $names = [];
    foreach ($xml->text->body->div as $div) {
      switch ($div['type']) {
        case '翻刻':
          $type = 'transcription';
          break;
        case '読み下し':
          $type = 'transliteration';
          break;
        case '現代語訳':
          $type = 'modernTranslation';
          break;
        case '解説':
          $type = 'commentary';
          break;
        default:
          $type = '';
          break;
      }
      $outputs[$type] = "";

      // DiDomライブラリによるパース
      $doc = new Document($div->asXML(), false, 'UTF-8', Document::TYPE_XML);

      $new = new Document(null, false, 'UTF-8', Document::TYPE_XML);

      // divタグの属性を削除
      $divTag = $doc->first('div')->removeAttribute('type');

      // 子要素を再帰的にhtml文字列に変換
      $converter = new ParseXMLString($new, $text_info['writeMode']);
      $newRoot = $converter->convert($divTag);

      // 結果をHTML文字列として取得
      $output = $newRoot->html();

      // typeごとに格納
      $outputs[$type] = $output;

      // 人名・地名保存配列
      $names[$type] = $converter->getNames();
    }

    // back処理
    // listPerson取得
    // 人名、地名の変数定義
    $people = [];
    $places = [];
    $firstPlaceId = null; // coordinate初期値取得用
    if ($xml->text->back) {
      $back = $xml->text->back;
      if ($back->listPerson) {
        // listPerson取得
        $listPerson = $back->listPerson;
        foreach ($listPerson->person as $value) {
          $personId = (string)($value->attributes('xml', true)['id'] ?? null);
          // テキスト内の人名保存配列からデータ取得
          if (array_key_exists($personId, $names['transcription']['persNames'])) {
            $persNames = $names['transcription']['persNames'][$personId];
          } else {
            $persNames = [];
          }
          // データを追加
          $people[$personId] = [
            'names' => $persNames,
            'note'  => (string)($value->note ?? ""),
            'idno'  => (string)($value->idno ?? ""),
          ];
        }
      }

      if ($back->listPlace) {
        // listPlace取得
        $listPlace = $back->listPlace;
        foreach ($listPlace->place as $value) {
          $placeId = (string)($value->attributes('xml', true)['id'] ?? null);
          // 最初のplaceIdを取得
          if ($firstPlaceId === null) {
            $firstPlaceId = $placeId;
          }
          // テキスト内の地名保存配列からデータ取得
          if (array_key_exists($placeId, $names['transcription']['placeNames'])) {
            $placeNames = $names['transcription']['placeNames'][$placeId];
          } else {
            $placeNames = [];
          }
          // データを追加
          $places[$placeId] = [
            'names' => $placeNames,
            'note'  => (string)($value->note ?? ""),
            'geo'   => (function ($geo) {
              // ノーブレークスペースをスペースに
              $geo = str_replace("\u{00A0}", " ", $geo);
              // 不要文字除去
              preg_match_all('/-?\d+(?:\.\d+)?/', $geo, $matches);
              $numbers = $matches[0];
              return count($numbers) == 2
                ? $numbers[0] . ' ' . $numbers[1]
                : '';
            })($value->location->geo ?? ''),
          ];
        }
      }
    }

    // place初期値取得
    $coordinate = $firstPlaceId ? [$places[$firstPlaceId]['geo'], $firstPlaceId] : [];

    // オーディオ
    $audio_url = '';

    return [
      'manifest_data' => $manifest_data,
      'tei_url' => $tei_url,
      'bibliography' => $bibliography,
      'zones' => $zones,
      'is_one_page' => $is_one_page,
      'text_info' => $text_info,
      'outputs' => $outputs,
      'people' => $people,
      'places' => $places,
      'coordinate' => $coordinate,
      'audio_url' => $audio_url,
    ];
  }
}
