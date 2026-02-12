<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Outfit extends Model
{
    use HasFactory;

    protected $table = 'outfits';

    protected $fillable = [
        'user_id',
        'file',
        'description',
        'outfit_date',
        'season',
        'tops',
        'outer',
        'bottoms',
        'shoes',
    ];

    private const RECENT_THRESHOLD_DAYS = 30;
    private const SEASON_COUNT = 4;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->where('outfit_date', $date);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'outfits_items', 'outfit_id', 'item_id');
    }

    protected static function booted()
    {
        static::deleting(function ($outfit) {
            // outfit_id が一致する通知を削除
            DatabaseNotification::where('data->outfit_id', $outfit->id)->delete();
        });
    }

    public function scopeUsesCategories($query, array $categories)
    {
        return $query->where(function ($q) use ($categories) {
            foreach ($categories as $category) {
                $q->orWhereNotNull($category);
            }
        });
    }

    //　特定ユーザーの投稿を除外
    public function scopeExcludeUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', '!=', $userId);
    }

    //　season 一致を優先表示する
    public function scopePreferSeason(Builder $query, ?int $season, CarbonImmutable $baseDate): Builder
    {
        $baseDateString = $baseDate->toDateString();
        $threshold = self::RECENT_THRESHOLD_DAYS;

        if (!$season) {
            return $query->orderByRaw(
                "ABS(DATEDIFF(outfit_date, ?)) ASC, LOG(likes_count + 1) DESC",
                [$baseDateString]
            );
        }

        return $query->orderByRaw(
            "
             CASE 
               WHEN season = ?
               AND ABS(DATEDIFF(outfit_date, ?)) <= {$threshold} THEN 0
               WHEN season = ? THEN 1
               WHEN LEAST(ABS(season - ?), {self::SEASON_COUNT} - ABS(season - ?)) = 1 THEN 2
               ELSE 3
             END,
             ABS(DATEDIFF(outfit_date, ?)) ASC,
             LOG(COALESCE(likes_count, 0) + 1) DESC
            ",
            [$season, $baseDateString, $season, $season, $season, $baseDateString]
        );
    }
}
