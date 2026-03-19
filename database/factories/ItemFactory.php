<?php

namespace Database\Factories;

use App\Domain\ClothingAdvice\ItemCategoryPolicy;
use App\Enums\Color;
use App\Enums\Gender;
use App\Enums\MainCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
  public function definition(): array
  {
    return [
      'file' => $this->generateItemImage(MainCategory::tops, null),
      'main_category' => MainCategory::tops,
      'sub_category' => null,
      'color' => fake()->randomElement(Color::getValues()),
      'season' => null,
      'memo' => fake()->optional()->sentence(),
    ];
  }

  public function mainCategory(int $mainCategory, $gender = null): static
  {
    return $this->state(function () use ($mainCategory, $gender) {
      $subCategory = $this->resolveSubCategoryByGender($mainCategory, $gender);

      return [
        'file' => $this->generateItemImage($mainCategory, $gender),
        'main_category' => $mainCategory,
        'sub_category' => $subCategory,
      ];
    });
  }

  private function resolveSubCategoryByGender(int $mainCategory, $gender): int
  {
    $subCategories = MainCategory::getSubCategories($mainCategory);

    return ItemCategoryPolicy::resolve($mainCategory, $gender, $subCategories);
  }


  public function tops($gender = null): static
  {
    return $this->mainCategory(MainCategory::tops, $gender);
  }

  public function bottoms($gender = null): static
  {
    return $this->mainCategory(MainCategory::bottoms, $gender);
  }

  public function shoes($gender = null): static
  {
    return $this->mainCategory(MainCategory::shoes, $gender);
  }

  public function outer($gender = null): static
  {
    return $this->mainCategory(MainCategory::outer, $gender);
  }

  public function accessories($gender = null): static
  {
    return $this->mainCategory(MainCategory::accessories, $gender);
  }

  private function generateItemImage(int $mainCategory, $gender = null): string
  {
    $folderMap = [
      MainCategory::tops => 'tops',
      MainCategory::bottoms => 'bottoms',
      MainCategory::shoes => 'shoes',
      MainCategory::outer => 'outer',
      MainCategory::accessories => 'accessories', // ← 将来用
    ];

    $folder = $folderMap[$mainCategory] ?? 'tops';

    $genderMap = [
      Gender::Male => 'mens',
      Gender::Female => 'womens',
      Gender::Kids => 'kids',
    ];

    $genderValue = $gender?->value;
    $genderFolder = $genderMap[$genderValue] ?? 'mens';

    $files = glob(public_path("dummy/items/{$genderFolder}/{$folder}/*.jpg"));

    if (empty($files)) {
      return "/dummy/items/{$genderFolder}/{$folder}/default.jpg";
    }

    $file = basename($files[array_rand($files)]);

    return "/dummy/items/{$genderFolder}/{$folder}/{$file}";
  }
}
