<?php

namespace App\Http\Controllers;

use App\Analyzers\WeatherAnalyzer;
use App\Domain\ClothingAdvice\SeasonResolver;
use App\Domain\Weather\WeatherDto;
use App\Http\Resources\OutfitResource;
use App\Models\User;
use App\Queries\HomeOutfitQuery;
use App\Services\WeatherService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        private SeasonResolver $seasonResolver,
        private WeatherService $weatherService,
        private WeatherAnalyzer $analyzer,
    ) {}

    // ホーム画面のおすすめユーザーのコーディネート取得
    public function index(HomeOutfitQuery $query)
    {
        $authUser = Auth::user();
        $genderFilter = request()->get('gender');
        $baseDate = CarbonImmutable::today();

        $lat = request()->get('lat');
        $lon = request()->get('lon');

        if ($lat && $lon) {

            $weatherData = $this->weatherService->getWeatherData($lat, $lon);

            if ($weatherData) {
                $weatherInfo = $this->analyzer
                    ->extractWeatherInfoForAdvice($weatherData, 0);

                $dto = WeatherDto::fromApi($weatherInfo, CarbonImmutable::now());

                $season = $dto->thermalSeason();
            } else {
                $season = $this->seasonResolver->resolve(now()->toDateString());
            }
        } else {
            $season = $this->seasonResolver->resolve($baseDate->toDateString());
        }

        $outfits = $query->get($season, $baseDate, $genderFilter, $authUser);

        return response()->json(['outfits' => OutfitResource::collection($outfits)->resolve()], 200);
    }

    // おすすめユーザー取得
    public function suggestionUsers()
    {
        $authUserId = Auth::id();
        $limit = request()->integer('limit', 5);

        // 推薦ユーザー取得ロジック
        $users = User::query()
            ->whereKeyNot($authUserId) // 本人除外

            ->whereDoesntHave('followers', function ($q) use ($authUserId) {
                $q->where('following_id', $authUserId);
            }) // 既にフォロー済み除外

            ->withCount([
                'outfits', // 投稿数
                'likesReceived as likes_count'
            ])
            ->orderByRaw('(outfits_count * 2 + likes_count) DESC') // 投稿数といいね数を基にソート

            ->limit(20)
            ->get()

            ->shuffle() // 毎回ランダム
            ->take($limit) // 5人表示
            ->values();

        return response()->json(['users' => $users], 200);
    }
}
