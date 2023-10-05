<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:jpg,jpeg,png',
                'main_category' => 'required',
                'sub_category' => 'nullable',
                'color' => 'required',
                'season' => 'nullable',
                'memo' => 'nullable',
            ],
            [
                'file.required' => 'ファイルを選択してください。',
                'file.mimes' => 'ファイル形式は jpg、jpeg、png のいずれかを選択してください。',
                'main_category.required' => 'メインカテゴリーを選択してください。',
                'color.required' => 'カラーを選択してください。',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = new Item();
        $item->user_id = auth()->user()->id;
        $item = (new FileService)->updateFile($item, $request, 'item');
        $item->main_category = $request->main_category;
        $item->sub_category = $request->sub_category;
        $item->color = $request->color;
        $item->season = $request->season;
        $item->memo = $request->input('memo');
        $item->save();
    }

    public function show(Item $item)
    {
        //
    }

    public function edit(Item $item)
    {
        //
    }

    public function update(Request $request, Item $item)
    {
        //
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (!empty($item->file)) {
            $currentFile = public_path() . $item->file;

            if (file_exists($currentFile)) {
                unlink($currentFile);
            }
        }

        $item->delete();
    }
}
