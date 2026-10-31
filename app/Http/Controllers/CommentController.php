<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Outfit;
use App\Notifications\OutfitCommented;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $outfit_id = $request->input('outfit_id');

        // すべてのコメントを取得し、最新のものが最初に来るようにします
        $comments = Comment::where('outfit_id', $outfit_id)->oldest()->get();

        // JSON形式でコメントを返します
        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outfit_id' => 'required|integer|exists:outfits,id',
            'text' => 'required|string|max:255'
        ]);

        $comment = new Comment;
        $comment->outfit_id = $validated['outfit_id'];
        $comment->user_id = Auth::id();
        $comment->text = $validated['text'];
        $comment->save();

        // コメント通知を送信
        $postByUser = $comment->outfit->user; // 投稿者を取得
        $commenter = Auth::user(); // コメントしたユーザーを取得

        // 自分がコメントした場合は通知を送らない
        if ($postByUser->id !== $commenter->id) {
            $postByUser->notify(new OutfitCommented($commenter, $comment->outfit, $comment));
        }

        return response()->json($comment, 201);
    }

    public function show($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'コメントが見つかりません'], 404);
        }
        return response()->json($comment, 200);
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['error' => 'コメントが見つかりません'], 404);
        }

        // コメントの投稿者本人か確認
        if (Auth::id() !== $comment->user_id) {
            abort(403);
        }

        $request->validate([
            'text' => 'required'
        ]);

        $comment->text = $request->input('text');
        $comment->save();

        return response()->json(['message' => 'コメントを更新しました'], 200);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['error' => 'コメントが見つかりません'], 404);
        }

        // コメントの投稿者本人か確認
        if (Auth::id() !== $comment->user_id) {
            abort(403);
        }

        $comment->delete();
        return response()->json(['message' => 'コメントを削除しました'], 200);
    }
}
