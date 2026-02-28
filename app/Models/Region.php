<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'selected_cities';

    protected $fillable = ['user_id', 'region_id', 'prefecture_id', 'city_id'];
}
