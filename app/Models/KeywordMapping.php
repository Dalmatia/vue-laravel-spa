<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeywordMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword',
        'main_category',
        'sub_category',
        'color',
        'style',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
