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

    public function index()
    {
        $items = Item::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
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

        // ファイルや必須項目が送信された場合のみバリデーションを実行
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
                if (!unlink($currentFile)) {
                    return response()->json(['error' => 'ファイルの削除に失敗しました'], 500);
                }
            }
        }

        $item->delete();

        return response()->json(['message' => 'アイテムを削除しました'], 200);
    }


    private function validateRequest(Request $request)
    {
        $rules = [
            'file' => 'required',
            'main_category' => 'required',
            'sub_category' => 'nullable',
            'color' => 'required',
            'season' => 'nullable',
            'memo' => 'nullable',
        ];

        // ファイルがアップロードされている場合のみバリデーションを追加
        if ($request->hasFile('file')) {
            $rules['file'] = 'mimes:jpg,jpeg,png';
        }

        $customMessages = [
            'file' => '登録アイテムを選択してください。',
            'file.mimes' => 'ファイル形式は jpg、jpeg、png のいずれかを選択してください。',
            'main_category' => 'メインカテゴリーを選択してください。',
            'color' => 'カラーを選択してください。'
        ];

        return Validator::make($request->all(), $rules, $customMessages);
    }
}
