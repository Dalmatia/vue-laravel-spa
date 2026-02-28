<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Notifications\OutfitLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function likes()
    {
        $userId = auth()->id();

        $likeIds = DB::table('likes')
            ->select(DB::raw('MAX(id) as id'))
            ->where('user_id', $userId)
            ->where('like', 1)
            ->groupBy('outfit_id')
            ->pluck('id');

        $likes = Like::with(['outfit.user'])
            ->whereIn('id', $likeIds)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($like) {
                return [
                    'id' => $like->id,
                    'outfit_id' => $like->outfit_id,
                    'outfit' => $like->outfit,
                    'user' => $like->outfit->user,
                ];
            });

        return response()->json(['likes' => $likes]);
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
