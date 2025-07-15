<?php

namespace App\Http\Response\Viewer;

use Inertia\Inertia;

/**
 * TEIの詳細画面レスポンスを生成する
 */
class ViewerResponse
{
  /**
   * TEIの表示データを処理し、viewに渡す
   *
   * @param array{
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
   *     audio_url: string,
   * } $data
   * @return \Inertia\Response
   */
  public function response(array $data): \Inertia\Response
  {
    return Inertia::render('Viewer', [
      'manifest_data' => $data['manifest_data'],
      'tei_url' => $data['tei_url'],
      'bibliography' => $data['bibliography'],
      'zones' => $data['zones'],
      'is_one_page' => $data['is_one_page'],
      'text_info' => $data['text_info'],
      'outputs' => $data['outputs'],
      'people' => $data['people'],
      'places' => $data['places'],
      'coordinate' => $data['coordinate'],
      'audio_url' => $data['audio_url'],
    ]);
  }
}
