<?php

namespace App\Http\Controllers;

use App\Domain\ClothingAdvice\SeasonResolver;
use App\Http\Resources\AllOutfitsCollection;
use App\Models\Outfit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(
        private SeasonResolver $seasonResolver
    ) {}

    public function index()
    {
        $baseDate = CarbonImmutable::today();

        $season = $this->seasonResolver->resolve($baseDate->toDateString());

        $outfits = Outfit::query()
            ->preferSeason($season, $baseDate)
            ->withCount([
                'likes as likes_count' => function ($q) {
                    $q->where('like', 1);
                }
            ])
            ->with(['user'])
            ->limit(5)
            ->get();

        return response([
            'outfits' => new AllOutfitsCollection($outfits),
            'users' => User::all()
        ]);
    }

    public function suggestionUsers()
    {
        $authUserId = Auth::id();

        $limit = request()->get('limit', 5);

        // 推薦ユーザー取得ロジック
        $suggestedUsers = User::query()
            ->where('id', '!=', $authUserId) // 本人除外
            ->whereDoesntHave('followers', function ($q) use ($authUserId) {
                $q->where('following_id', $authUserId);
            }) // 既にフォロー済み除外
            ->withCount([
                'outfits', // 投稿数
                'likesReceived as likes_count' => function ($q) {
                    $q->select(DB::raw('count(*)'));
                }
            ])
            ->orderByRaw('(outfits_count * 2 + likes_count) DESC') // 投稿数といいね数を基にソート
            ->limit(50)
            ->inRandomOrder() // 毎回ランダム
            ->limit($limit) // 5人表示
            ->get();

        return response()->json(['users' => $suggestedUsers]);
    }
}
