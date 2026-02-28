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

    // ユニークIDを生成
    $uniqueId = uniqid('', true);
    // 元の形式で保存
    // $originalExtension = $request->file('file')->getClientOriginalExtension();
    // $originalHashName = md5($uniqueId . $request->file('file')->getClientOriginalName()) . '.' . $originalExtension;
    // $file->save(public_path() . $directory . $originalHashName);

    // WebP形式で保存
    $webpHashName = md5($uniqueId . $request->file('file')->getClientOriginalName()) . '.webp';
    $file->encode('webp', 90);
    $file->save(public_path() . $directory . $webpHashName);
    // モデルにはWebP形式のパスを保存
    $model->file = $directory . $webpHashName;

    return $model;
  }
}
