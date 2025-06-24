<?php

namespace App\Http\Controllers;

use App\Enums\Cities;
use Illuminate\Http\Request;
use App\Enums\Regions;
use App\Models\Region;

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

    public function saveSelectedLocation(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
            'prefecture_id' => 'required|integer',
            'city_id' => 'required|integer',
        ]);

        $user_id = auth()->user()->id;
        // 既に保存されている地域情報があれば更新、なければ新規作成
        $region = Region::where('user_id', $user_id)->first();
        if ($region) {
            $region->update([
                'region_id' => $request->input('region_id'),
                'prefecture_id' => $request->input('prefecture_id'),
                'city_id' => $request->input('city_id'),
            ]);

            return response()->json(['message' => '地域情報を更新しました。']);
        } else {
            Region::create([
                'user_id' => $user_id,
                'region_id' => $request->input('region_id'),
                'prefecture_id' => $request->input('prefecture_id'),
                'city_id' => $request->input('city_id'),
            ]);

            return response()->json(['message' => '地域情報を保存しました。']);
        }
    }

    public function getSavedLocation()
    {
        $user_id = auth()->user()->id;
        $savedRegion = Region::where('user_id', $user_id)->first();

        if (!$savedRegion) {
            return response()->json(['message' => '地域情報が見つかりません。'], 404);
        }

        // 市町村情報を取得
        $cityData = Cities::getCityById($savedRegion->prefecture_id, $savedRegion->city_id);

        return response()->json([
            'region_id' => $savedRegion->region_id,
            'prefecture_id' => $savedRegion->prefecture_id,
            'city_id' => $savedRegion->city_id,
            'city_name' => $cityData['name'] ?? null,
            'latitude' => $cityData['lat'] ?? null,
            'longitude' => $cityData['lon'] ?? null,
        ]);
    }
}
