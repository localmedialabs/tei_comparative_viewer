<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Response\Viewer\UploadResponse;
use App\Http\Services\Viewer\ViewerService;
use App\Http\Response\Viewer\ViewerResponse;

use App\Rules\UploadCheck;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Inertia\Response as InertiaResponse;

class ViewerController
{

  /**
   * TEIファイルのアップロード画面を表示する
   *
   * @param UploadResponse $response
   * @return InertiaResponse
   */
  public function form(UploadResponse $response): InertiaResponse
  {
    return $response->response();
  }

  /**
   * TEIファイルの内容をチェックする
   *
   * @param Request $request
   * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response
   */
  public function validate(Request $request): Application|ResponseFactory|\Illuminate\Foundation\Application|Response
  {
    $request->validate([
      'tei' => ['required', 'file', 'mimes:xml', new UploadCheck],
    ]);
    return response('');
  }

  /**
   *  ビューワーを表示する
   *
   * @param Request $request
   * @param ViewerService $service
   * @param ViewerResponse $response
   * @return InertiaResponse
   */
  public function viewer(Request $request, ViewerService $service, ViewerResponse $response): InertiaResponse
  {
    $data = $service(
      $request->file('tei'),
      $request->input('tei_url', '')
    );
    return $response->response($data);
  }
}
