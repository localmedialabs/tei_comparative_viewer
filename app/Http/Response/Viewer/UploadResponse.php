<?php

namespace App\Http\Response\Viewer;

use Inertia\Inertia;

/**
 * TEIファイルのアップロード画面のレスポンスを生成する
 */
class UploadResponse
{
  /**
   * TEIファイルのアップロード画面の表示データを処理し、viewに渡す
   *
   * @return \Inertia\Response
   */
  public function response(): \Inertia\Response
  {
    return Inertia::render('Form/Index');
  }
}
