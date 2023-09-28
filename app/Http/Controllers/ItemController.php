<?php

namespace App\Http\Controllers;

use App\Enums\Color;
use App\Enums\MainCategory;
use App\Enums\Season;
use App\Enums\SubCategory;
use App\Models\Item;
use App\Services\FileService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        $mainCategories = MainCategory::toSelectArray();
        $subCategories = SubCategory::toSelectArray();
        $colors = Color::toSelectArray();
        $seasons = Season::toSelectArray();

        return view('items.create', compact('mainCategories', 'subCategories', 'colors', 'seasons'));
    }

    public function store(Request $request)
    {
        $item = new Item();
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png',
            'main_category' => 'required',
            'sub_category' => 'required',
            'color' => 'required',
            'season' => 'required',
        ]);

        $item->user_id = auth()->user()->id;
        $item = (new FileService)->updateFile($item, $request, 'item');
        $item->main_category = $request->main_category;
        $item->sub_category = $request->sub_category;
        $item->color = $request->color;
        $item->season = $request->season;
        $item->memo = $request->input('text');
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

    public function destroy(Item $item)
    {
        //
    }
}
