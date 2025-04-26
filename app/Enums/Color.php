<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Color extends Enum
{
    const black = 1;
    const white = 2;
    const gray = 3;
    const red = 4;
    const navy = 5;
    const blue = 6;
    const light_blue = 7;
    const green = 8;
    const olive = 9;
    const brown = 10;
    const beige = 11;
    const purple = 12;
    const yellow = 13;
    const orange = 14;
    const pink = 15;
    const neon = 16;
    const border = 17;
    const patterned = 18;
    const denim = 19;
    const silver = 20;
    const gold = 21;
    const other = 22;

    protected static $labels = [
        self::black => 'ブラック',
        self::white => 'ホワイト',
        self::gray => 'グレー',
        self::red => 'レッド',
        self::navy => 'ネイビー',
        self::blue => 'ブルー',
        self::light_blue => 'ライトブルー',
        self::green => 'グリーン',
        self::olive => 'オリーブ',
        self::brown => 'ブラウン',
        self::beige => 'ベージュ',
        self::purple => 'パープル',
        self::yellow => 'イエロー',
        self::orange => 'オレンジ',
        self::pink => 'ピンク',
        self::neon => 'ネオン',
        self::border => 'ボーダー柄',
        self::patterned => 'パターン柄',
        self::denim => 'デニム',
        self::silver => 'シルバー',
        self::gold => 'ゴールド',
        self::other => 'その他',
    ];

    protected static $hexCodes = [
        self::black => '#000000',
        self::white => '#ffffff',
        self::gray => '#9ca3af',
        self::red => '#ff0000',
        self::navy => '#1e3a8a',
        self::blue => '#60a5fa',
        self::light_blue => '#93c5fd',
        self::green => '#4ade80',
        self::olive => '#808000',
        self::brown => '#a0522d',
        self::beige => '#f5f5dc',
        self::purple => '#a78bfa',
        self::yellow => '#fde047',
        self::orange => '#fb923c',
        self::pink => '#f9a8d4',
        self::neon => '#39ff14',
        self::border => '#d1d5db',     // グレー系想定
        self::patterned => '#e5e7eb',  // 明るめグレー想定
        self::denim => '#1e40af',
        self::silver => '#c0c0c0',
        self::gold => '#ffd700',
        self::other => '#d4d4d4',
    ];


    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しないカラーです';
    }

    public static function getValue($key): ?int
    {
        $keyValueMap = [
            'ブラック' => self::black,
            'ホワイト' => self::white,
            'グレー' => self::gray,
            'レッド' => self::red,
            'ネイビー' => self::navy,
            'ブルー' => self::blue,
            'ライトブルー' => self::light_blue,
            'グリーン' => self::green,
            'オリーブ' => self::olive,
            'ブラウン' => self::brown,
            'ベージュ' => self::beige,
            'パープル' => self::purple,
            'イエロー' => self::yellow,
            'オレンジ' => self::orange,
            'ピンク' => self::pink,
            'ネオン' => self::neon,
            'ボーダー柄' => self::border,
            'パターン柄' => self::patterned,
            'デニム' => self::denim,
            'シルバー' => self::silver,
            'ゴールド' => self::gold,
            'その他' => self::other,
        ];

        return $keyValueMap[$key] ?? null;
    }

    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::getValues() as $value) {
            $selectArray[] = [
                'value' => $value,
                'name' => static::getDescription($value),
                'hex' => static::$hexCodes[$value] ?? '#cccccc', // fallback color
            ];
        }

        return $selectArray;
    }
}
