<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * 市町村区の情報を管理するEnum
 * 各市町村に ID, 名前, 緯度・経度を格納する
 */
final class Cities extends Enum
{
    const CITIES = [
        Prefectures::HOKKAIDO => [
            ['id' => 1, 'name' => '旭川市', 'lat' => 43.7706, 'lon' => 142.3648],
            ['id' => 2, 'name' => '釧路市', 'lat' => 42.984932, 'lon' => 144.381775],
            ['id' => 3, 'name' => '札幌市', 'lat' => 43.062077, 'lon' => 141.354401],
            ['id' => 4, 'name' => '函館市', 'lat' => 41.768688, 'lon' => 140.728943],
        ],
        Prefectures::AOMORI => [
            ['id' => 1, 'name' => '青森市', 'lat' => 40.822639, 'lon' => 140.746964],
            ['id' => 2, 'name' => '八戸市', 'lat' => 40.512302, 'lon' => 141.488449],
        ],
        Prefectures::IWATE => [
            ['id' => 1, 'name' => '宮古市', 'lat' => 39.639553, 'lon' => 141.946121],
            ['id' => 2, 'name' => '盛岡市', 'lat' => 39.702068, 'lon' => 141.154465],
        ],
        Prefectures::MIYAGI => [
            ['id' => 1, 'name' => '白石市', 'lat' => 38.002445, 'lon' => 140.619904],
            ['id' => 2, 'name' => '仙台市', 'lat' => 38.268162, 'lon' => 140.869507],
        ],
        Prefectures::AKITA => [
            ['id' => 1, 'name' => '秋田市', 'lat' => 39.719959, 'lon' => 140.103516],
            ['id' => 2, 'name' => '横手市', 'lat' => 39.313801, 'lon' => 140.566711],
        ],
        Prefectures::YAMAGATA => [
            ['id' => 1, 'name' => '酒田市', 'lat' => 38.914639, 'lon' => 139.836548],
            ['id' => 2, 'name' => '山形市', 'lat' => 38.255459, 'lon' => 140.339767],
        ],
        Prefectures::FUKUSHIMA => [
            ['id' => 1, 'name' => 'いわき市', 'lat' => 37.050552, 'lon' => 140.887802],
            ['id' => 2, 'name' => '福島市', 'lat' => 37.760830, 'lon' => 140.474747],
        ],
        Prefectures::IBARAKI => [
            ['id' => 1, 'name' => '土浦市', 'lat' => 36.078354, 'lon' => 140.204193],
            ['id' => 2, 'name' => '水戸市', 'lat' => 36.365852, 'lon' => 140.471588],
        ],
        Prefectures::TOCHIGI => [
            ['id' => 1, 'name' => '宇都宮市', 'lat' => 36.555115, 'lon' => 139.882599],
            ['id' => 2, 'name' => '大田原市', 'lat' => 36.870922, 'lon' => 140.015839],
        ],
        Prefectures::GUNMA => [
            ['id' => 1, 'name' => '前橋市', 'lat' => 36.389462, 'lon' => 139.063461],
            ['id' => 2, 'name' => 'みなかみ町', 'lat' => 36.678684, 'lon' => 138.999069],
        ],
        Prefectures::SAITAMA => [
            ['id' => 1, 'name' => '熊谷市', 'lat' => 36.147221, 'lon' => 139.388611],
            ['id' => 2, 'name' => 'さいたま市', 'lat' => 35.861683, 'lon' => 139.645676],
            ['id' => 3, 'name' => '秩父市', 'lat' => 35.991596, 'lon' => 139.085251],
        ],
        Prefectures::CHIBA => [
            ['id' => 1, 'name' => '館山市', 'lat' => 34.996559, 'lon' => 139.869965],
            ['id' => 2, 'name' => '千葉市', 'lat' => 35.607037, 'lon' => 140.106064],
        ],
        Prefectures::TOKYO => [
            ['id' => 1, 'name' => '大島町', 'lat' => 34.750206, 'lon' => 139.355515],
            ['id' => 2, 'name' => '千代田区', 'lat' => 35.693831, 'lon' => 139.753647],
            ['id' => 3, 'name' => '渋谷区', 'lat' => 35.663670, 'lon' => 139.697723],
            ['id' => 4, 'name' => '八王子市', 'lat' => 35.666780, 'lon' => 139.315910],
        ],
        Prefectures::KANAGAWA => [
            ['id' => 1, 'name' => '横浜市', 'lat' => 35.450336, 'lon' => 139.634216],
            ['id' => 2, 'name' => '川崎市', 'lat' => 35.530829, 'lon' => 139.703178],
        ],
        Prefectures::NIIGATA => [
            ['id' => 1, 'name' => '新潟市', 'lat' => 37.916077, 'lon' => 139.036545],
            ['id' => 2, 'name' => '長岡市', 'lat' => 37.446533, 'lon' => 138.851486],
        ],
        Prefectures::TOYAMA => [
            ['id' => 1, 'name' => '富山市', 'lat' => 36.695904, 'lon' => 137.213715],
            ['id' => 2, 'name' => '高岡市', 'lat' => 36.754015, 'lon' => 137.025604],
        ],
        Prefectures::ISHIKAWA => [
            ['id' => 1, 'name' => '金沢市', 'lat' => 36.560945, 'lon' => 136.656532],
            ['id' => 2, 'name' => '輪島市', 'lat' => 37.390553, 'lon' => 136.899170],
        ],
        Prefectures::FUKUI => [
            ['id' => 1, 'name' => '福井市', 'lat' => 36.064075, 'lon' => 136.219589],
            ['id' => 2, 'name' => '敦賀市', 'lat' => 35.645588, 'lon' => 136.055435],
        ],
        Prefectures::YAMANASHI => [
            ['id' => 1, 'name' => '甲府市', 'lat' => 35.662231, 'lon' => 138.568298],
            ['id' => 2, 'name' => '山梨市', 'lat' => 35.693273, 'lon' => 138.687286],
        ],
        Prefectures::NAGANO => [
            ['id' => 1, 'name' => '松本市', 'lat' => 36.238056, 'lon' => 137.971954],
            ['id' => 2, 'name' => '長野市', 'lat' => 36.648537, 'lon' => 138.194824],
        ],
        Prefectures::GIFU => [
            ['id' => 1, 'name' => '岐阜市', 'lat' => 35.426201, 'lon' => 136.759933],
            ['id' => 2, 'name' => '大垣市', 'lat' => 35.359936, 'lon' => 136.612930],
        ],
        Prefectures::SHIZUOKA => [
            ['id' => 1, 'name' => '静岡市', 'lat' => 34.975185, 'lon' => 138.383286],
            ['id' => 2, 'name' => '浜松市', 'lat' => 34.710808, 'lon' => 137.726303],
        ],
        Prefectures::AICHI => [
            ['id' => 1, 'name' => '名古屋市', 'lat' => 35.181438, 'lon' => 136.906570],
            ['id' => 2, 'name' => '豊橋市', 'lat' => 34.769169, 'lon' => 137.391388],
        ],
        Prefectures::MIE => [
            ['id' => 1, 'name' => '津市', 'lat' => 34.718613, 'lon' => 136.505676],
            ['id' => 2, 'name' => '四日市市', 'lat' => 34.965118, 'lon' => 136.624451],
        ],
        Prefectures::SHIGA => [
            ['id' => 1, 'name' => '大津市', 'lat' => 35.017776, 'lon' => 135.854721],
            ['id' => 2, 'name' => '彦根市', 'lat' => 35.274445, 'lon' => 136.259720],
        ],
        Prefectures::KYOTO => [
            ['id' => 1, 'name' => '京都市', 'lat' => 35.011669, 'lon' => 135.768112],
            ['id' => 2, 'name' => '舞鶴市', 'lat' => 35.474724, 'lon' => 135.386108],
        ],
        Prefectures::OSAKA => [
            ['id' => 1, 'name' => '大阪市', 'lat' => 34.693890, 'lon' => 135.502228],
            ['id' => 2, 'name' => '堺市', 'lat' => 34.573299, 'lon' => 135.483055],
        ],
        Prefectures::HYOGO => [
            ['id' => 1, 'name' => '神戸市', 'lat' => 34.689404, 'lon' => 135.195694],
            ['id' => 2, 'name' => '姫路市', 'lat' => 34.815277, 'lon' => 134.685562],
        ],
        Prefectures::NARA => [
            ['id' => 1, 'name' => '奈良市', 'lat' => 34.685001, 'lon' => 135.804718],
            ['id' => 2, 'name' => '大和高田市', 'lat' => 34.515892, 'lon' => 135.737457],
        ],
        Prefectures::WAKAYAMA => [
            ['id' => 1, 'name' => '串本市', 'lat' => 33.485687, 'lon' => 135.787018],
            ['id' => 2, 'name' => '和歌山市', 'lat' => 34.230556, 'lon' => 135.170837],
        ],
        Prefectures::TOTTORI => [
            ['id' => 1, 'name' => '鳥取市', 'lat' => 35.494453, 'lon' => 134.222153],
            ['id' => 2, 'name' => '米子市', 'lat' => 35.428158, 'lon' => 133.330948],
        ],
        Prefectures::SHIMANE => [
            ['id' => 1, 'name' => '松江市', 'lat' => 35.467940, 'lon' => 133.048553],
            ['id' => 2, 'name' => '浜田市', 'lat' => 34.899166, 'lon' => 132.080002],
        ],
        Prefectures::OKAYAMA => [
            ['id' => 1, 'name' => '岡山市', 'lat' => 34.655186, 'lon' => 133.919754],
            ['id' => 2, 'name' => '倉敷市', 'lat' => 34.584888, 'lon' => 133.771935],
        ],
        Prefectures::HIROSHIMA => [
            ['id' => 1, 'name' => '広島市', 'lat' => 34.385277, 'lon' => 132.455276],
            ['id' => 2, 'name' => '福山市', 'lat' => 34.485832, 'lon' => 133.362503],
        ],
        Prefectures::YAMAGUCHI => [
            ['id' => 1, 'name' => '下関市', 'lat' => 33.957287, 'lon' => 130.940964],
            ['id' => 2, 'name' => '山口市', 'lat' => 34.178333, 'lon' => 131.473892],
        ],
        Prefectures::TOKUSHIMA => [
            ['id' => 1, 'name' => '徳島市', 'lat' => 34.070091, 'lon' => 134.554703],
            ['id' => 2, 'name' => '鳴門市', 'lat' => 34.172897, 'lon' => 134.608955],
        ],
        Prefectures::KAGAWA => [
            ['id' => 1, 'name' => '高松市', 'lat' => 34.342758, 'lon' => 134.046524],
            ['id' => 2, 'name' => '丸亀市', 'lat' => 34.289843, 'lon' => 133.798714],
        ],
        Prefectures::EHIME => [
            ['id' => 1, 'name' => '松山市', 'lat' => 33.839165, 'lon' => 132.765640],
            ['id' => 2, 'name' => '新居浜市', 'lat' => 33.960297, 'lon' => 133.283401],
        ],
        Prefectures::KOCHI => [
            ['id' => 1, 'name' => '高知市', 'lat' => 33.558723, 'lon' => 133.531097],
            ['id' => 2, 'name' => '室戸市', 'lat' => 33.289932, 'lon' => 134.152023],
        ],
        Prefectures::FUKUOKA => [
            ['id' => 1, 'name' => '福岡市', 'lat' => 33.590000, 'lon' => 130.401672],
            ['id' => 2, 'name' => '北九州市', 'lat' => 33.883411, 'lon' => 130.875229],
        ],
        Prefectures::SAGA => [
            ['id' => 1, 'name' => '佐賀市', 'lat' => 33.263512, 'lon' => 130.300888],
            ['id' => 2, 'name' => '唐津市', 'lat' => 33.449777, 'lon' => 129.967575],
        ],
        Prefectures::NAGASAKI => [
            ['id' => 1, 'name' => '長崎市', 'lat' => 32.749527, 'lon' => 129.879807],
            ['id' => 2, 'name' => '佐世保市', 'lat' => 33.179920, 'lon' => 129.715088],
        ],
        Prefectures::KUMAMOTO => [
            ['id' => 1, 'name' => '熊本市', 'lat' => 32.803333, 'lon' => 130.708054],
            ['id' => 2, 'name' => '八代市', 'lat' => 32.507500, 'lon' => 130.601807],
        ],
        Prefectures::OITA => [
            ['id' => 1, 'name' => '大分市', 'lat' => 33.239445, 'lon' => 131.609726],
            ['id' => 2, 'name' => '別府市', 'lat' => 33.284620, 'lon' => 131.491241],
        ],
        Prefectures::MIYAZAKI => [
            ['id' => 1, 'name' => '宮崎市', 'lat' => 31.907778, 'lon' => 131.420273],
            ['id' => 2, 'name' => '延岡市', 'lat' => 32.582176, 'lon' => 131.665070],
        ],
        Prefectures::KAGOSHIMA => [
            ['id' => 1, 'name' => '鹿児島市', 'lat' => 31.596861, 'lon' => 130.557022],
            ['id' => 2, 'name' => '鹿屋市', 'lat' => 31.378332, 'lon' => 130.852219],
        ],
        Prefectures::OKINAWA => [
            ['id' => 1, 'name' => '那覇市', 'lat' => 26.212374, 'lon' => 127.679108],
            ['id' => 2, 'name' => '石垣市', 'lat' => 24.344419, 'lon' => 124.185249],
        ],
    ];

    /**
     * 都道府県 ID から市町村一覧を取得
     */
    public static function getCitiesByPrefecture(int $prefectureId): array
    {
        return self::CITIES[$prefectureId] ?? [];
    }

    /**
     * 市町村名から都道府県 ID を取得
     */
    public static function getPrefectureByCity(string $cityName): ?int
    {
        foreach (self::CITIES as $prefectureId => $cities) {
            foreach ($cities as $city) {
                if ($city['name'] === $cityName) {
                    return $prefectureId;
                }
            }
        }
        return null;
    }

    /**
     * 都道府県 ID と市町村 ID から市町村情報を取得
     */
    public static function getCityById(int $prefectureId, int $cityId): ?array
    {
        return self::CITIES[$prefectureId][$cityId] ?? null;
    }

    /**
     * 市町村名から緯度・経度を取得
     */
    public static function getCoordinatesByCity(string $cityName): ?array
    {
        foreach (self::CITIES as $cities) {
            foreach ($cities as $city) {
                if ($city['name'] === $cityName) {
                    return ['lat' => $city['lat'], 'lon' => $city['lon']];
                }
            }
        }
        return null;
    }

    /**
     * すべての都道府県と市町村データを取得
     */
    public static function getAllCities(): array
    {
        return self::CITIES;
    }
}
