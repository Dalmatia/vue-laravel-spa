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
    const black = 0;
    const white = 1;
    const gray = 2;
    const red = 3;
    const navy = 4;
    const blue = 5;
    const light_blue = 6;
    const green = 7;
    const olive = 8;
    const yellow = 9;
    const orange = 10;
    const neon = 11;
    const border = 12;
    const patterned = 13;
    const denim = 14;
    const other = 15;

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
        self::yellow => 'イエロー',
        self::orange => 'オレンジ',
        self::neon => 'ネオン',
        self::border => 'ボーダー柄',
        self::patterned => 'パターン柄',
        self::denim => 'デニム',
        self::other => 'その他',
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
            'イエロー' => self::yellow,
            'オレンジ' => self::orange,
            'ネオン' => self::neon,
            'ボーダー柄' => self::border,
            'パターン柄' => self::patterned,
            'デニム' => self::denim,
            'その他' => self::other,
        ];

        return $keyValueMap[$key] ?? null;
    }

    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::getValues() as $value) {
            $selectArray[$value] = static::getDescription($value);
        }

        return $selectArray;
    }
}
