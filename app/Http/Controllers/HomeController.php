<?php

namespace App\Http\Controllers;

use App\Analyzers\WeatherAnalyzer;
use App\Domain\ClothingAdvice\SeasonResolver;
use App\Domain\Weather\WeatherDto;
use App\Enums\Gender;
use App\Http\Resources\AllOutfitsCollection;
use App\Models\Outfit;
use App\Models\User;
use App\Services\WeatherService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(
        private SeasonResolver $seasonResolver,
        private WeatherService $weatherService,
        private WeatherAnalyzer $analyzer,
    ) {}

    public function index()
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

        $outfits = Outfit::query()
            ->preferSeason($season, $baseDate)
            ->when(
                filled($genderFilter),
                function ($query) use ($genderFilter) {
                    if ($genderFilter !== 'all') {
                        $query->whereHas('user', function ($q) use ($genderFilter) {
                            $q->where('gender', $genderFilter);
                        });
                    }
                }
            )
            ->when(
                !$genderFilter && $authUser && $authUser->gender !== Gender::NotSet,
                function ($query) use ($authUser) {
                    $query->whereHas('user', function ($q) use ($authUser) {
                        $q->where('gender', $authUser->gender);
                    });
                }
            )
            ->withCount([
                'likes as likes_count' => function ($q) {
                    $q->where('like', 1);
                }
            ])
            ->with(['user', 'items'])
            ->limit(5)
            ->get();

        return response(['outfits' => new AllOutfitsCollection($outfits)]);
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
