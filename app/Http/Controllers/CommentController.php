<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Outfit;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $outfit_id = $request->input('outfit_id');

        // すべてのコメントを取得し、最新のものが最初に来るようにします
        $comments = Comment::where('outfit_id', $outfit_id)->latest()->get();

        // JSON形式でコメントを返します
        return response()->json(['comments' => $comments]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'outfit_id' => 'required',
            'user_id' => 'required',
            'comment' => 'required'
        ]);

        $comment = new Comment;

        $comment->outfit_id = $request->input('outfit_id');
        $comment->user_id = $request->input('user_id');
        $comment->text = $request->input('comment');
        $comment->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);
        $comment->delete();
    }
}
