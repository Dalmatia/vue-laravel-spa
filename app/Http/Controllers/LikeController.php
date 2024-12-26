<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllOutfitsCollection;
use App\Models\Like;
use App\Models\Outfit;
use App\Notifications\OutfitLiked;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function likes()
    {
        $user = auth()->user()->id;

        // いいねしたコーディネートのIDの配列を取得
        $likes = Like::where('user_id', $user)
            ->where('like', 1)
            ->latest()
            ->pluck('outfit_id')
            ->toArray();

        // 対応するコーディネートを一度に取得
        $outfits = Outfit::whereIn('id', $likes)->get();

        // いいねしたコーディネートの一覧を取得
        $likes = Like::whereIn('outfit_id', $likes)->where('like', 1)->orderBy('created_at', 'desc')->get();

        return response()->json(['likes' => $likes, 'outfits' => new AllOutfitsCollection($outfits)]);
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
        // いいね! 通知を送信
        $postByUser = $like->outfit->user; // 投稿者を取得
        if ($postByUser->id !== $user->id) { // 自分の投稿にいいね！した場合は通知を送らない
            $postByUser->notify(new OutfitLiked($user, $like->outfit));
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
