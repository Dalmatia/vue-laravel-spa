<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\Gender;
use App\Enums\MainCategory;
use App\Enums\SubCategory;

class ItemCategoryPolicy
{
  // 性別と大カテゴリに基づいて、使用するサブカテゴリを決定するポリシー
  private const ALLOWED = [
    Gender::Male => [
      MainCategory::bottoms => [
        SubCategory::pants,
        SubCategory::denimPants,
        SubCategory::slacks,
        SubCategory::chinoPants,
        SubCategory::cargoPants,
        SubCategory::sweatPants,
        SubCategory::widePants,
      ],
      MainCategory::shoes => [
        SubCategory::sneaker,
        SubCategory::leather_shoes,
        SubCategory::boots,
        SubCategory::loafers,
        SubCategory::sandals,
        SubCategory::runningShoes,
      ],
    ],

    Gender::Female => [
      MainCategory::bottoms => [
        SubCategory::pants,
        SubCategory::denimPants,
        SubCategory::shorts,
        SubCategory::skirt,
        SubCategory::pleatedSkirt,
        SubCategory::longSkirt,
      ],
      MainCategory::shoes => [
        SubCategory::sneaker,
        SubCategory::sandals,
        SubCategory::pumps,
        SubCategory::heels,
      ],
    ],
  ];

  // 性別と大カテゴリに基づいて、使用しないサブカテゴリを決定するポリシー
  private const EXCLUDED = [
    Gender::Male => [
      MainCategory::tops => [
        SubCategory::blouse,
      ],
    ],
  ];

  // Factoryなどで、性別と大カテゴリに基づいてサブカテゴリを決定するためのメソッド
  public static function resolve(int $mainCategory, ?Gender $gender, array $allSubCategories): int
  {
    if (!$gender) {
      return fake()->randomElement(array_column($allSubCategories, 'id'));
    }

    // allowed優先
    $allowed = self::getAllowed($mainCategory, $gender);
    if ($allowed) {
      return fake()->randomElement($allowed);
    }

    // allowedがない場合は、excludedを考慮してフィルタリング
    $filtered = self::filter($mainCategory, $gender, $allSubCategories);

    if (!empty($filtered)) {
      return fake()->randomElement(array_column($filtered, 'id'));
    }

    return fake()->randomElement(array_column($allSubCategories, 'id'));
  }

  public static function getAllowed(int $mainCategory, Gender $gender): ?array
  {
    return self::ALLOWED[$gender->value][$mainCategory] ?? null;
  }

  public static function getExcluded(int $mainCategory, Gender $gender): array
  {
    return self::EXCLUDED[$gender->value][$mainCategory] ?? [];
  }

  public static function filter(int $mainCategory, Gender $gender, array $subCategories): array
  {
    $excluded = self::getExcluded($mainCategory, $gender);

    return array_filter($subCategories, function ($sub) use ($excluded) {
      return !in_array($sub['id'], $excluded);
    });
  }
}
