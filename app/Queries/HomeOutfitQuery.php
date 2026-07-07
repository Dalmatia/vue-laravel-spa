<?php

namespace App\Queries;

use App\Enums\Gender;
use App\Models\Outfit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeOutfitQuery
{
  public function get(?int $season, ?int $scene, CarbonImmutable $baseDate, ?string $genderFilter, ?User $authUser)
  {
    $outfits = Outfit::query()
      ->withCount([
        'likes as likes_count' => fn($q) => $q->where('like', 1)
      ])

      ->when(
        $authUser,
        fn($query) => $query->excludeUser($authUser->id)
      )
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

      ->preferScene($scene)
      ->preferSeason($season, $baseDate)

      ->withExists([
        'likes as is_liked' => fn($q) =>
        $q->where('user_id', Auth::id())->where('like', 1)
      ])

      ->with([
        'user:id,name,file',
        'items' => fn($q) => $q->orderByPivot('role')
      ])

      ->limit(5)
      ->get();

    return $outfits;
  }
}
