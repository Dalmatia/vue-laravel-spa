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
}
