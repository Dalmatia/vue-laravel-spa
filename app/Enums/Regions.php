<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * 日本の地域（地方）を表す Enum
 * 
 * 地域 ID を定義し、ラベルと変換メソッドを提供する。
 */
final class Regions extends Enum
{
    public const HOKKAIDO_TOHOKU = 1;
    public const KANTO = 2;
    public const CHUBU = 3;
    public const KINKI = 4;
    public const CHUGOKU = 5;
    public const SHIKOKU = 6;
    public const KYUSHU_OKINAWA = 7;

    private static array $labels = [
        self::HOKKAIDO_TOHOKU => '北海道/東北',
        self::KANTO => '関東',
        self::CHUBU => '中部',
        self::KINKI => '近畿',
        self::CHUGOKU => '中国',
        self::SHIKOKU => '四国',
        self::KYUSHU_OKINAWA => '九州/沖縄',
    ];

    private static array $regionPrefectures = [
        self::HOKKAIDO_TOHOKU => [
            Prefectures::HOKKAIDO,
            Prefectures::AOMORI,
            Prefectures::IWATE,
            Prefectures::MIYAGI,
            Prefectures::AKITA,
            Prefectures::YAMAGATA,
            Prefectures::FUKUSHIMA
        ],
        self::KANTO => [
            Prefectures::IBARAKI,
            Prefectures::TOCHIGI,
            Prefectures::GUNMA,
            Prefectures::SAITAMA,
            Prefectures::CHIBA,
            Prefectures::TOKYO,
            Prefectures::KANAGAWA
        ],
        self::CHUBU => [
            Prefectures::NIIGATA,
            Prefectures::TOYAMA,
            Prefectures::ISHIKAWA,
            Prefectures::FUKUI,
            Prefectures::YAMANASHI,
            Prefectures::NAGANO,
            Prefectures::GIFU,
            Prefectures::SHIZUOKA,
            Prefectures::AICHI
        ],
        self::KINKI => [
            Prefectures::MIE,
            Prefectures::SHIGA,
            Prefectures::KYOTO,
            Prefectures::OSAKA,
            Prefectures::HYOGO,
            Prefectures::NARA,
            Prefectures::WAKAYAMA
        ],
        self::CHUGOKU => [
            Prefectures::TOTTORI,
            Prefectures::SHIMANE,
            Prefectures::OKAYAMA,
            Prefectures::HIROSHIMA,
            Prefectures::YAMAGUCHI
        ],
        self::SHIKOKU => [
            Prefectures::TOKUSHIMA,
            Prefectures::KAGAWA,
            Prefectures::EHIME,
            Prefectures::KOCHI
        ],
        self::KYUSHU_OKINAWA => [
            Prefectures::FUKUOKA,
            Prefectures::SAGA,
            Prefectures::NAGASAKI,
            Prefectures::KUMAMOTO,
            Prefectures::OITA,
            Prefectures::MIYAZAKI,
            Prefectures::KAGOSHIMA,
            Prefectures::OKINAWA
        ]
    ];

    public static function toList(): array
    {
        return array_map(
            fn($id, $name) => ['id' => $id, 'name' => $name],
            array_keys(self::$labels),
            self::$labels
        );
    }

    public static function getPrefecturesByRegion(int $regionId): array
    {
        if (!isset(self::$regionPrefectures[$regionId])) {
            return null; // 未定義の地域IDなら null を返す
        }

        $prefectureIds = self::$regionPrefectures[$regionId] ?? [];
        return array_map(
            fn($id) => ['id' => $id, 'name' => Prefectures::getLabel($id)] ?? '不明',
            $prefectureIds
        );
    }
}
