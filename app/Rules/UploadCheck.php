<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UploadCheck implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    try {
      $xml = simplexml_load_file($value);
    } catch (\Exception $e) {
      $fail('TEIファイルの読み込みに失敗しました。タグの不一致等を確認してください。エラーメッセージ：' . $e->getMessage());
      return;
    }
    if (!$xml->teiHeader) {
      $fail('teiHeaderタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc) {
      $fail('teiHeader/fileDescタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc->titleStmt) {
      $fail('teiHeader/fileDesc/titleStmtタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc->titleStmt->title) {
      $fail('teiHeader/fileDesc/titleStmt/titleタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc->titleStmt->author) {
      $fail('teiHeader/fileDesc/authorStmt/authorタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc->publicationStmt) {
      $fail('teiHeader/fileDesc/publicationStmtタグが存在しません。');
    } elseif (!$xml->teiHeader->fileDesc->sourceDesc) {
      $fail('teiHeader/fileDesc/sourceDescタグが存在しません。');
    }
    if (!$xml->facsimile) {
      $fail('facsimileタグが存在しません。');
    } elseif (!$xml->facsimile['sameAs']) {
      $fail('facsimileタグにsameAs属性が存在しません。');
    }
    if ($xml->facsimile->surface) {
      foreach ($xml->facsimile->surface as $surface) {
        if (!$surface['sameAs']) {
          $fail('sameAs属性が存在しないsurfaceタグがあります。');
        } elseif (!$surface['ulx']) {
          $fail('ulx属性が存在しないsurfaceタグがあります。');
        } elseif (!$surface['uly']) {
          $fail('uly属性が存在しないsurfaceタグがあります。');
        } elseif (!$surface['lrx']) {
          $fail('lrx属性が存在しないsurfaceタグがあります。');
        } elseif (!$surface['lry']) {
          $fail('lry属性が存在しないsurfaceタグがあります。');
        } elseif (!$surface->attributes('xml', true)['id']) {
          $fail('xml:idが存在しないsurfaceタグがあります。');
        } elseif (!$surface->label) {
          $fail('labelタグが存在しないsurfaceタグがあります。');
        } elseif (!$surface->graphic) {
          $fail('graphicタグが存在しないsurfaceタグがあります。');
        } elseif (!$surface->graphic['width']) {
          $fail('width属性が存在しないgraphicタグがあります。');
        } elseif (!$surface->graphic['height']) {
          $fail('height属性が存在しないgraphicタグがあります。');
        } elseif (!$surface->graphic['url']) {
          $fail('url属性が存在しないgraphicタグがあります。');
        } elseif (!$surface->graphic['sameAs']) {
          $fail('sameAs属性が存在しないgraphicタグがあります。');
        } elseif ($surface->zone) {
          if (!$surface->zone->attributes('xml', true)['id']) {
            $fail('xml:idが存在しないzoneタグがあります。');
          }
        }
      }
    } else {
      $fail('facsimile/surfaceタグが存在しません。');
    }
    if (!$xml->text) {
      $fail('textタグが存在しません。');
    } elseif (!$xml->text->body) {
      $fail('text/bodyタグが存在しません。');
    } elseif (!$xml->text->body->div) {
      $fail('text/body/divタグが存在しません。');
    } elseif (!$xml->text->body->div['type']) {
      $fail('type属性が存在しないdivタグがあります。');
    }

    // 定義済みタグの確認
    $definedTags = [
      "TEI",
      "teiHeader",
      "fileDesc",
      "titleStmt",
      "title",
      "author",
      "editor",
      "respStmt",
      "resp",
      "name",
      "publicationStmt",
      "publisher",
      "date",
      "availability",
      "licence",
      "notesStmt",
      "note",
      "sourceDesc",
      "bibl",
      "biblScope",
      "msDesc",
      "msIdentifier",
      "country",
      "settlement",
      "institution",
      "repository",
      "collection",
      "idno",
      "msContents",
      "summary",
      "msItem",
      "colophon",
      "physDesc",
      "objectDesc",
      "supportDesc",
      "support",
      "extent",
      "dimensions",
      "height",
      "width",
      "depth",
      "condition",
      "decoDesc",
      "decoNote",
      "additions",
      "ab",
      "bindingDesc",
      "binding",
      "p",
      "sealDesc",
      "history",
      "origin",
      "origDate",
      "provenance",
      "acquisition",
      "profileDesc",
      "correspDesc",
      "correspAction",
      "persName",
      "textClass",
      "keywords",
      "term",
      "encodingDesc",
      "projectDesc",
      "editorialDecl",
      "facsimile",
      "surface",
      "label",
      "graphic",
      "zone",
      "text",
      "body",
      "div",
      "pb",
      "ab",
      "s",
      "lb",
      "placeName",
      "metamark",
      "ruby",
      "rb",
      "rt",
      "milestone",
      "anchor",
      "add",
      "del",
      "subst",
      "figure",
      "head",
      "back",
      "listPerson",
      "person",
      "idno",
      "listPlace",
      "place",
      "location",
      "geo",
    ];
    $flashError = "";
    $tagNames = getTagNames($xml);
    $undefinedTags = array_diff($tagNames, $definedTags);
    if ($undefinedTags) {
      // 致命的なエラーではないのでフラッシュメッセージとして確認画面に出力
      $flashError .= '未定義のタグが存在します。【' . implode(', ', $undefinedTags) . '】' . PHP_EOL;
    }

    // 定義済み属性の確認
    $definedAttributes = [
      "corresp",
      "target",
      "when",
      "from",
      "to",
      "notBefore",
      "notAfter",
      "unit",
      "ref",
      "type",
      "sameAs",
      "xml:id",
      "ulx",
      "uly",
      "lrx",
      "lry",
      "width",
      "height",
      "url",
      "style",
      "xml:lang",
      "n",
      "facs",
      "xmll:id",
      "function"
    ];
    $attributeNames = getAllAttributeNames($xml);
    $undefinedAttributes = array_diff($attributeNames, $definedAttributes);
    if ($undefinedAttributes) {
      // 属性とタグの対応データ取得
      $attributeTagMap = [];
      getSpecifiedAttributesWithTags($xml, $undefinedAttributes, $attributeTagMap);

      // 属性名（タグ名）の形式に変換
      $resultStrings = [];
      foreach ($attributeTagMap as $attrName => $tagNames) {
        foreach ($tagNames as $tagName) {
          $resultStrings[] = "{$attrName}（タグ名：{$tagName}）";
        }
      }
      // 致命的なエラーではないのでフラッシュメッセージとして確認画面に出力
      $flashError .= '未定義の属性が存在します。【' . implode(', ', $resultStrings) . '】' . PHP_EOL;
    }

    // 定義済み属性値の確認
    // type
    $definedTypeValues = [
      'URI',
      'cover',
      'caption',
      'alt',
      'sent',
      'received',
      '翻刻',
      '読み下し',
      '現代語訳',
      '解説',
      '割書',
      '注',
      'noteEnd'
    ];
    $typeValues = getAttributeValues($xml, 'type');
    $undefinedTypeValues = array_diff($typeValues, $definedTypeValues);
    if ($undefinedTypeValues) {
      // 致命的なエラーではないのでフラッシュメッセージとして確認画面に出力
      $flashError .= '未定義のtype属性の値が存在します。【' . implode(', ', $undefinedTypeValues) . '】' . PHP_EOL;
    }

    // unit
    $definedUnitValues = [
      'wlb',
      'wbr',
      'cm',
      'volume',
    ];
    $unitValues = getAttributeValues($xml, 'unit');
    $undefinedUnitValues = array_diff($unitValues, $definedUnitValues);
    if ($undefinedUnitValues) {
      // 致命的なエラーではないのでフラッシュメッセージとして確認画面に出力
      $flashError .= '未定義のunit属性の値が存在します。【' . implode(', ', $undefinedUnitValues) . '】' . PHP_EOL;
    }

    // function
    $definedFunctionValues = [
      'kaeri',
    ];
    $functionValues = getAttributeValues($xml, 'function');
    $undefinedFunctionValues = array_diff($functionValues, $definedFunctionValues);
    if ($undefinedFunctionValues) {
      // 致命的なエラーではないのでフラッシュメッセージとして確認画面に出力
      $flashError .= '未定義のfunction属性の値が存在します。【' . implode(', ', $undefinedFunctionValues) . '】' . PHP_EOL;
    }

    // $flashErrorが空でなかったらフラッシュメッセージをセット
    if ($flashError !== "") {
      session()->flash('error', $flashError);
    }
  }
}
