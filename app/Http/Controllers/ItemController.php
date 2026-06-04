<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemService;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    private $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index()
    {
        $items = Item::ownedBy(Auth::id())->latest()->get();
        return response()->json(['items' => $items], 200);
    }

    public function store(StoreItemRequest $request)
    {
        $item = new Item();
        $item = $this->itemService->fillItemFromRequest($item, $request);
        $item->save();

        return response()->json($item, 200);
    }

    public function categoryItems($mainCategory)
    {
        $items = Item::ownedBy(Auth::id())
            ->mainCategory($mainCategory)
            ->latest()
            ->paginate(18);

        return response()->json($items);
    }

    public function show($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'アイテムが見つかりません'], 404);
        }
        return response()->json($item, 200);
    }

    public function update(UpdateItemRequest $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'アイテムが見つかりません'], 404);
        }

        if (Auth::id() !== $item->user_id) {
            return abort(403);
        }

        // ファイルや必須項目が送信された場合のみバリデーションを実行
        $item = $this->itemService->fillItemFromRequest($item, $request);
        $item->save();

        return response()->json($item, 200);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['error' => 'アイテムが見つかりません'], 404);
        }

        if (!empty($item->file)) {
            $currentFile = public_path() . $item->file;

            if (file_exists($currentFile)) {
                if (!unlink($currentFile)) {
                    Log::error('ファイルの削除に失敗しました: ' . $currentFile);
                    return response()->json(['error' => 'ファイルの削除に失敗しました'], 500);
                }
            }
        }

        $item->delete();

        return response()->json(['message' => 'アイテムを削除しました'], 200);
    }
}
