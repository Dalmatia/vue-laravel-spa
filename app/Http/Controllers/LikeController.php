<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
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
        $request->validate(['outfit_id' => 'required']);

        $like = new Like();
        $like->user_id = auth()->user()->id;
        $like->outfit_id = $request->input('outfit_id');
        $like->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Like $like)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $like = Like::find($id);
        if (count(collect($like)) > 0) {
            $like->delete();
        }
    }
}
