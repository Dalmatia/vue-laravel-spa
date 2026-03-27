<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutfitRequest;
use App\Http\Requests\UpdateOutfitRequest;
use App\Http\Resources\OutfitResource;
use App\Models\Outfit;
use App\Models\User;
use App\Services\OutfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutfitController extends Controller
{
    protected $outfitService;
    public function __construct(OutfitService $outfitService)
    {
        $this->outfitService = $outfitService;
    }

    public function index(Request $request)
    {
        $query = Outfit::with(['items', 'user'])
            ->withCount([
                'likes as likes_count' => function ($query) {
                    $query->where('like', 1);
                }
            ]);

        // フィルタリング条件を取得
        $filters = [
            'gender' => $request->query('gender'),
            'main_category' => $request->query('mainCategory'),
            'sub_category' => $request->query('subCategory'),
            'color' => $request->query('color'),
            'season' => $request->query('season')
        ];

        // フィルタリング条件に応じてクエリを構築
        if ($filters['gender'] !== null && $filters['gender'] !== '') {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('gender', $filters['gender']);
            });
        }

        $query->when(
            $filters['main_category'] || $filters['sub_category'] || $filters['color'],
            function ($query) use ($filters) {
                $query->whereHas('items', function ($q) use ($filters) {
                    if ($filters['main_category']) {
                        $q->where('main_category', $filters['main_category']);
                    }

                    if ($filters['sub_category']) {
                        $q->where('sub_category', $filters['sub_category']);
                    }

                    if ($filters['color']) {
                        $q->where('color', $filters['color']);
                    }
                });
            }
        );

        if ($filters['season']) {
            $query->where('season', $filters['season']);
        }

        // ソート条件のマッピング
        $sortOptions = [
            'popular' => ['likes_count', 'desc'],
            'latest'  => ['outfit_date', 'desc'],
            'oldest'  => ['outfit_date', 'asc'],
        ];

        $sort = $request->query('sort', 'popular'); // デフォルトは 'popular'

        // 対応するソート条件があるかチェック
        if (isset($sortOptions[$sort])) {
            [$column, $direction] = $sortOptions[$sort];
            $query->orderBy($column, $direction);
        }

        // 結果を取得
        $outfits = $query->paginate(12);

        // コーディネートを取得し、ユーザー情報も取得
        return response()->json([
            'outfits' => OutfitResource::collection($outfits->items()),
            'meta' => [
                'current_page' => $outfits->currentPage(),
                'last_page' => $outfits->lastPage(),
                'has_more' => $outfits->hasMorePages(),
            ],
        ], 200);
    }

    public function store(StoreOutfitRequest $request)
    {
        $outfit = new Outfit();
        $outfit = $this->outfitService->createOrUpdateOutfit($outfit, $request);
        return response()->json($outfit, 200);
    }

    public function show($id)
    {
        // Outfit を user リレーション込みで取得（N+1問題対策）
        $outfit = Outfit::with([
            'user',
            'items' => function ($query) {
                $query->orderByPivot('role');
            }
        ])->find($id);

        // コーディネートが見つからない場合
        if (!$outfit) {
            return response()->json([
                'message' => 'お探しのコーディネートが見つかりません。',
            ], 404);
        }

        // ユーザーが見つからない場合
        if (!$outfit->user) {
            return response()->json([
                'message' => 'コーディネートに関連付けられたユーザー情報が見つかりません。',
            ], 404);
        }

        // 正常なレスポンス
        return response()->json([
            'outfit' => $outfit,
            'user' => $outfit->user
        ], 200);
    }


    public function update(UpdateOutfitRequest $request, $id)
    {
        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['error' => 'コーディネートが見つかりません'], 404);
        }

        if (Auth::id() !== $outfit->user_id) {
            return abort(403);
        }

        $outfit = $this->outfitService->createOrUpdateOutfit($outfit, $request);
        return response()->json($outfit, 200);
    }

    public function destroy($id)
    {
        $outfit = Outfit::find($id);
        if (!$outfit) {
            return response()->json(['error' => 'コーディネートが見つかりません'], 404);
        }

        if ($outfit->file && file_exists(public_path($outfit->file))) {
            unlink(public_path($outfit->file));
        }

        $outfit->delete();
        return response()->json(['message' => 'コーディネートを削除しました'], 200);
    }
}
