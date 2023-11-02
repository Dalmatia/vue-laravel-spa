<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
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
    private function createOrUpdateItem(Item $item, Request $request)
    {
        $item->user_id = auth()->user()->id;
        // ファイルが選択されているかどうかをチェックし、選択されている場合のみ更新する
        if ($request->hasFile('file')) {
            $item = $this->fileService->updateFile($item, $request, 'item');
        } elseif ($request->input('file') === null && $item->file !== null) {
            // ファイルが選択されていない場合は以前のファイルを保持する
            $item->file = $item->file;
        }

        // メインカテゴリーやカラーが空でないかをチェックし、空でない場合のみ更新する
        if ($request->filled('main_category')) {
            $item->main_category = $request->main_category;
        }

        if ($request->filled('sub_category')) {
            $item->sub_category = $request->sub_category;
        }

        if ($request->filled('color')) {
            $item->color = $request->color;
        }

        if ($request->filled('season')) {
            $item->season = $request->season;
        }

        if ($request->filled('memo')) {
            $item->memo = $request->input('memo');
        }

        return $item;
    }

    public function index()
    {
        $items = Item::all();
        return response()->json(['items' => $items], 200);
    }

    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = new Item();
        $item = $this->createOrUpdateItem($item, $request);
        $item->save();

        return response()->json($item, 200);
    }

    public function show($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'アイテムが見つかりません'], 404);
        }
        return response()->json($item, 200);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (auth()->user()->id !== $item->user_id) {
            return abort(403);
        }

        // ファイルが更新された場合のみバリデーションを無効にする
        if ($request->hasFile('file')) {
            $this->validateRequest($request);
        }

        $item = $this->createOrUpdateItem($item, $request);
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
                unlink($currentFile);
            }
        }

        $item->delete();

        return response()->json(['message' => 'アイテムを削除しました'], 200);
    }


    private function validateRequest(Request $request)
    {
        $rules = [
            'file' => 'nullable|mimes:jpg,jpeg,png',
            'main_category' => 'nullable',
            'sub_category' => 'nullable',
            'color' => 'nullable',
            'season' => 'nullable',
            'memo' => 'nullable',
        ];

        $customMessages = [
            'file' => '登録アイテムを選択してください。',
            'file.mimes' => 'ファイル形式は jpg、jpeg、png のいずれかを選択してください。',
            'main_category' => 'メインカテゴリーを選択してください。',
            'color' => 'カラーを選択してください。'
        ];

        // 必須フィールドが空でない場合は、必須バリデーションを適用する
        if ($request->filled('file') || $request->filled('main_category') || $request->filled('color')) {
            $rules['file'] = 'required|mimes:jpg,jpeg,png';
            $rules['main_category'] = 'required';
            $rules['color'] = 'required';
        }

        return Validator::make($request->all(), $rules, $customMessages);
    }
}
