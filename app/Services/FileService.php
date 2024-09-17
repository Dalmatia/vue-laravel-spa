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

    $ext = $request->file('file');
    $extension = $ext->getClientOriginalExtension();
    $name = time() . '.' . $extension;
    $file->save(public_path() . $directory . $name);
    $model->file = $directory . $name;

    return $model;
  }
}
