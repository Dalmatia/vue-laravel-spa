<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->where('outfit_date', $date);
    }

    public function Likes()
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
}
