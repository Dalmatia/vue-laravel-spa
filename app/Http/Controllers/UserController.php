<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllOutfitsCollection;
use App\Models\Outfit;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);
        if ($user === null) {
            return redirect()->route('home.index');
        }

        $outfits = Outfit::with(['comments', 'user'])
            ->withCount(['likes as likes_count' => function ($query) {
                $query->where('like', 1);
            }, 'comments as comments_count'])
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user' => $user,
            'outfits' => new AllOutfitsCollection($outfits),
            'outfit_count' => $outfits->count(),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id, // 他のユーザーと重複しないように
                'file' => 'nullable|mimes:jpg,jpeg,png|max:2048', // ファイルは必須ではなく、サイズの制限を追加
                'gender' => 'nullable|integer|in:0,1,2,3',
                'birthdate' => 'nullable|date',
            ]);

            // プロフィール画像の更新（ファイルがある場合のみ）
            if ($request->hasFile('file')) {
                // ファイル更新処理をサービス経由で実行
                $user = (new FileService)->updateFile($user, $request, 'user');
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->gender = $validated['gender'] ?? null;
            $user->birthdate = $validated['birthdate'] ?: null;
            $user->save();

            return response()->json(['message' => 'プロフィールを更新しました'], 200);
        } catch (\Throwable $e) {
            Log::error('プロフィール更新エラー: ', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'プロフィールの更新に失敗しました'], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // 現在のパスワード確認
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => '現在のパスワードが正しくありません'], 422);
        }
        // パスワードが入力された場合のみハッシュ化して保存
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'パスワードの更新が完了しました'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
