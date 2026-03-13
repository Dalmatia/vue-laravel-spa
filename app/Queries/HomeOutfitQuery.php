<?php

namespace App\Queries;

use App\Enums\Gender;
use App\Models\Outfit;
use Illuminate\Support\Facades\Auth;

class HomeOutfitQuery
{
  public function get($season, $baseDate, $genderFilter, $authUser)
  {
    return Outfit::query()
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
        'likes as likes_count' => fn($q) => $q->where('like', 1)
      ])

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
  }
}
