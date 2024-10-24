<?php

namespace App\Services;

use Image;

class FileService
{
  public function updateFile($model, $request, $type)
  {
    if (!empty($model->file)) {
      $currentFile = public_path() . $model->file;

      if (file_exists($currentFile) && $currentFile != public_path() . '/user-placeholder.png') {
        unlink($currentFile);
      }
    }

    // 保存するディレクトリをタイプごとに分ける
    $directory = '/file/';  // デフォルトのディレクトリ
    if ($type === "user") {
      $directory = '/user/';
    } elseif ($type === "item") {
      $directory = '/item/';
    } elseif ($type === "outfit") {
      $directory = '/outfit/';
    }

    $file = null;
    if ($type === "user") {
      $file = Image::make($request->file('file'))->resize(400, 400);
    } else {
      $file = Image::make($request->file('file'));
    }

    // 拡張子を取得
    $extension = $request->file('file')->getClientOriginalExtension();
    // ハッシュ化したファイル名を生成
    $hashName = md5(time() . $request->file('file')->getClientOriginalName()) . '.' . $extension;
    $file->save(public_path() . $directory . $hashName);
    $model->file = $directory . $hashName;

    return $model;
  }
}
