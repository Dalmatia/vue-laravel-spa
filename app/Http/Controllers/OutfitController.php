<?php

namespace App\Http\Controllers;

use App\Models\Outfit;
use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Support\Facades\Validator;

class OutfitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required | mimes:jpg,jpeg,png',
            'description' => 'nullable',
            'outfit_date' => 'required',
            'season' => 'nullable',
            'tops' => 'nullable |exists:items,id',
            'outer' => 'nullable |exists:items,id',
            'bottoms' => 'nullable |exists:items,id',
            'shoes' => 'nullable |exists:items,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $outfit = new Outfit();
        $outfit->user_id = auth()->user()->id;
        $outfit = (new FileService)->updateFile($outfit, $request, 'outfit');
        $outfit->description = $request->input('description');

        // コーディネートした日付を選択する
        $outfit->outfit_date = $request->input('outfit_date');
        // 指定された日付に既に投稿があるか確認
        $validator->after(function ($validator) use ($outfit) {
            if (Outfit::onDate($outfit->outfit_date)->where('user_id', $outfit->user_id)->exists()) {
                $validator->errors()->add('outfit_date', '同じ日付に複数の投稿はできません。');
            }
        });

        // バリデーションが失敗した場合（2回目のチェック）
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // コーディネートのシーズンを選択
        $outfit->season = $request->input('season');
        // 着用したアイテムをItemテーブルから選択
        $outfit->tops = $request->input('tops');
        $outfit->outer = $request->input('outer');
        $outfit->bottoms = $request->input('bottoms');
        $outfit->shoes = $request->input('shoes');
        $outfit->save();

        return response()->json($outfit, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Outfit $outfit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outfit $outfit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Outfit $outfit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outfit $outfit)
    {
        //
    }
}
