<?php

namespace App\Http\Controllers;

use App\Models\Outfit;
use Illuminate\Http\Request;
use App\Services\FileService;

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
        $outfit = new Outfit();

        $request->validate([
            'outfit' => 'required | mimes:jpg,jpeg,png',
            'description' => 'nullable',
            'outfit_date' => 'required',
            'season' => 'required',
            'tops' => 'nullable |exists:items,id',
            'outer' => 'nullable |exists:items,id',
            'bottoms' => 'nullable |exists:items,id',
            'shoes' => 'nullable |exists:items,id'
        ]);

        $outfit->user_id = auth()->user()->id;
        $outfit->outfit = (new fileService)->updateFile($outfit, $request, 'outfit');
        $outfit->description = $request->input('description');
        // コーディネートした日付を選択する
        $outfit->outfit_date = $request->input('outfit_date');
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
