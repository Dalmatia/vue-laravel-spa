<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class MainCategory extends Enum
{
    const outer = 1;
    const tops = 2;
    const bottoms = 3;
    const shoes = 4;
    const accessories = 5;

    protected static $labels = [
        self::outer => 'アウター',
        self::tops => 'トップス',
        self::bottoms => 'ボトムス',
        self::shoes => 'シューズ',
        self::accessories => 'アクセサリー'
    ];

    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しないメインカテゴリーです';
    }

    public static function getValue($key): ?int
    {
        $keyValueMap = [
            'アウター' => self::outer,
            'トップス' => self::tops,
            'ボトムス' => self::bottoms,
            'シューズ' => self::shoes,
            'アクセサリー' => self::accessories
        ];

        return $keyValueMap[$key] ?? null;
    }

    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::getValues() as $value) {
            $selectArray[] = [
                'id' => $value,
                'name' => static::getDescription($value),
            ];
        }

        return $selectArray;
    }

    protected static $subCategoryMap = [
        self::outer => [
            SubCategory::tailoredJacket,
            SubCategory::blazer,
            SubCategory::blouson,
            SubCategory::coat,
            SubCategory::trenchCoat,
            SubCategory::downJacket,
            SubCategory::windbreaker,
            SubCategory::cardiganJacket,
            SubCategory::vestOuter,
            SubCategory::formal_suit,
        ],
        self::tops => [
            SubCategory::tShirt,
            SubCategory::longSleeveTee,
            SubCategory::shirt,
            SubCategory::poloShirt,
            SubCategory::parka,
            SubCategory::sweatshirt,
            SubCategory::knit,
            SubCategory::cardigan,
            SubCategory::vest,
            SubCategory::blouse,
            SubCategory::thermalShirt,
        ],
        self::bottoms => [
            SubCategory::pants,
            SubCategory::denimPants,
            SubCategory::slacks,
            SubCategory::chinoPants,
            SubCategory::cargoPants,
            SubCategory::sweatPants,
            SubCategory::widePants,
            SubCategory::shorts,
            SubCategory::skirt,
            SubCategory::pleatedSkirt,
            SubCategory::longSkirt,
        ],
        self::shoes => [
            SubCategory::sneaker,
            SubCategory::leather_shoes,
            SubCategory::boots,
            SubCategory::loafers,
            SubCategory::sandals,
            SubCategory::runningShoes,
            SubCategory::pumps,
            SubCategory::heels,
            SubCategory::slippers,
        ],
        self::accessories => [
            SubCategory::cap,
            SubCategory::beanie,
            SubCategory::scarf,
            SubCategory::gloves,
            SubCategory::necktie,
            SubCategory::bag,
            SubCategory::belt,
            SubCategory::other,
        ],
    ];

    public static function getSubCategories(int $mainCategory): array
    {
        $subCategoryIds = static::$subCategoryMap[$mainCategory] ?? [];

        return array_map(function ($id) {
            return [
                'id' => $id,
                'name' => SubCategory::getDescription($id),
            ];
        }, $subCategoryIds);
    }
}
