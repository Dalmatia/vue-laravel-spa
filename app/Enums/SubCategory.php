<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SubCategory extends Enum
{
    const tShirt = 0;
    const shirt = 1;
    const poloShirt = 2;
    const parka = 3;
    const sweatshirt = 4;
    const knit = 5;
    const pants = 6;
    const denimPants = 7;
    const skirt = 8;
    const jacket = 9;
    const coat = 10;
    const sneaker = 11;
    const leather_shoes = 12;
    const boots = 13;
    const formal_suit = 14;
    const other = 15;

    protected static $labels = [
        self::tShirt => 'Tシャツ',
        self::shirt => 'シャツ',
        self::poloShirt => 'ポロシャツ',
        self::parka => 'パーカー',
        self::sweatshirt => 'スウェット',
        self::knit => 'ニット',
        self::pants => 'パンツ',
        self::denimPants => 'デニムパンツ',
        self::skirt => 'スカート',
        self::jacket => 'ジャケット/ブルゾン',
        self::coat => 'コート',
        self::sneaker => 'スニーカー',
        self::leather_shoes => '革靴',
        self::boots => 'ブーツ',
        self::formal_suit => 'フォーマルスーツ',
        self::other => 'その他',
    ];


    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しないサブカテゴリーです';
    }

    public static function getValue($key): ?int
    {
        $keyValueMap = [
            'Tシャツ' => self::tShirt,
            'シャツ' => self::shirt,
            'ポロシャツ' => self::poloShirt,
            'パーカー' => self::parka,
            'スウェット' => self::sweatshirt,
            'ニット' => self::knit,
            'パンツ' => self::pants,
            'デニムパンツ' => self::denimPants,
            'スカート' => self::skirt,
            'ジャケット/ブルゾン' => self::jacket,
            'コート' => self::coat,
            'スニーカー' => self::sneaker,
            '革靴' => self::leather_shoes,
            'ブーツ' => self::boots,
            'フォーマルスーツ' => self::formal_suit,
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