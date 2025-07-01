<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemService
{
  // FileServiceのインスタンス化
  private $fileService;

  public function __construct(FileService $fileService)
  {
    $this->fileService = $fileService;
  }

  /**
   * アイテムのフィールドを更新
   */
  public function fillItemFromRequest(Item $item, Request $request)
  {
    $item->user_id = auth()->id();

    // ファイルが選択されているかどうかをチェックし、選択されている場合のみ更新する
    if ($request->hasFile('file')) {
      $item = $this->fileService->updateFile($item, $request, 'item');
    }

    // メインカテゴリーやカラーが空でないかをチェックする。サブカテゴリー等null許容項目に関しては更新しない場合はnullを返す。
    if ($request->filled('main_category')) {
      $item->main_category = $request->main_category;
    }

    if ($request->filled('sub_category')) {
      $item->sub_category = ($request->sub_category !== 'null') ? $request->sub_category : null;
    }

    if ($request->filled('color')) {
      $item->color =  $request->color;
    }

    if ($request->filled('season')) {
      $item->season =  ($request->season !== 'null') ? $request->season : null;
    }

    if ($request->filled('memo')) {
      $item->memo =  ($request->memo !== 'null') ? $request->memo : null;
    }
    return $item;
  }
}
