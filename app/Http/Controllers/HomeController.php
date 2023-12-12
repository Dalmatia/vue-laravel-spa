<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllOutfitsCollection;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $outfits = Outfit::orderBy('created_at', 'desc')->get();

        return response(['outfits' => new AllOutfitsCollection($outfits), 'users' => User::all()]);
    }
}
