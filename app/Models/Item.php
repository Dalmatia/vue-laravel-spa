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
}
