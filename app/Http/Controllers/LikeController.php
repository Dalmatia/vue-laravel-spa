<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Outfit;
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
    public function firstcheck($outfit)
    {
        $user = auth()->user();
        $likes = new Like();
        $like = Like::where('outfit_id', $outfit)->where('user_id', $user->id)->first();
        if ($like) {
            $count = $likes->where('outfit_id', $outfit)->where('like', 1)->count();
            return [$like->like, $count];
        } else {
            $like = $likes->create([
                'user_id' => $user->id,
                'outfit_id' => $outfit,
                'like' => 0
            ]);
            $count = $likes->where('outfit_id', $outfit)->where('like', 1)->count();
            return [$like->like, $count];
        }
    }

    public function like($outfit)
    {
        $user = auth()->user();
        $likes = new Like();
        $like = Like::where('outfit_id', $outfit)->where('user_id', $user->id)->first();
        if (!$like) {
            $like = $likes->create([
                'user_id' => $user->id,
                'outfit_id' => $outfit,
                'like' => 1
            ]);
        } else {
            $like->like = 1;
            $like->save();
        }
        $count = $likes->where('outfit_id', $outfit)->where('like', 1)->count();
        return response()->json(['count' => $count]);
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
    public function unlike($outfit)
    {
        $user = auth()->user();
        $likes = new Like();
        $like = Like::where('outfit_id', $outfit)->where('user_id', $user->id)->first();
        if ($like) {
            $like->like = 0;
            $like->save();
        }
        $count = $likes->where('outfit_id', $outfit)->where('like', 1)->count();
        return response()->json(['count' => $count]);
    }
}
