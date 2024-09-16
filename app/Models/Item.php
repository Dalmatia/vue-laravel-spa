<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'user_id',
        'file',
        'main_category',
        'sub_category',
        'color',
        'season',
        'memo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outfits()
    {
        return $this->belongsToMany(Outfit::class, 'outfits_items', 'item_id', 'outfit_id');
    }
}
