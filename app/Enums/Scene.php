<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Scene extends Enum
{
  const casual = 1;
  const date = 2;
  const office = 3;
  const outdoor = 4;

  protected static $labels = [
    self::casual => 'カジュアル',
    self::date => 'デート',
    self::office => 'オフィス',
    self::outdoor => 'アウトドア',
  ];


  public static function getDescription($value): string
  {
    return static::$labels[$value] ?? '存在しないシーンです';
  }

  public static function toSelectArray(): array
  {
    return collect(static::getValues())
      ->map(fn($value) => [
        'id' => $value,
        'key' => static::getKey($value),
        'name' => static::getDescription($value),
      ])
      ->values()
      ->all();
  }
}
