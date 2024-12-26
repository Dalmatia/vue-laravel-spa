<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_id',
        'like'
    ];

    public function outfit()
    {
        return $this->belongsTo(Outfit::class);
    }
}
