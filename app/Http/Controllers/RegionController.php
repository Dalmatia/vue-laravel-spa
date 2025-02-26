<?php

namespace App\Http\Controllers;

use App\Enums\Cities;
use Illuminate\Http\Request;
use App\Enums\Regions;


class RegionController extends Controller
{
    // 全地域を取得
    public function getRegions()
    {
        return response()->json([
            'regions' => Regions::toList(),
        ]);
    }

    // 特定の地域の都道府県を取得
    public function getPrefs(Request $request)
    {
        $request->validate([
            'region' => 'required|integer',
        ]);

        $region = $request->query('region');
        $prefs = Regions::getPrefecturesByRegion($region);

        if (!$prefs) {
            return response()->json(['error' => '該当の地域が見つかりません。'], 404);
        }

        return response()->json([
            'region' => $region,
            'prefectures' => $prefs,
        ]);
    }

    // 特定の都道府県の市区町村を取得
    public function getCities(Request $request)
    {
        $request->validate([
            'prefecture' => 'required|integer',
        ]);

        $prefecture = $request->query('prefecture');
        $cities = Cities::getCitiesByPrefecture($prefecture);

        if (!$cities) {
            return response()->json(['error' => '該当の都道府県が見つかりません。'], 404);
        }

        return response()->json([
            'prefecture' => $prefecture,
            'cities' => $cities,
        ]);
    }
}
