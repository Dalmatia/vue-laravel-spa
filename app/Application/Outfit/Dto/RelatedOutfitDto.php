<?php

namespace App\Application\Outfit\Dto;

use App\Models\Outfit;

class RelatedOutfitDto
{
  public function __construct(
    public int $id,
    public string $file,
    public int $likesCount,
    public array $user,
    public array $usedParts,
    public ?string $outfitDate,
  ) {}

  public static function fromModel(Outfit $outfit): self
  {
    return new self(
      id: $outfit->id,
      file: $outfit->file,
      likesCount: $outfit->likes_count ?? 0,
      user: [
        'id' => $outfit->user->id,
        'name' => $outfit->user->name,
        'file' => $outfit->user->file ?? null,
      ],
      usedParts: self::extractCategories($outfit),
      outfitDate: $outfit->outfit_date,
    );
  }

  private static function extractCategories($outfit): array
  {
    return collect(['tops', 'outer', 'bottoms', 'shoes'])
      ->filter(fn($cat) => !is_null($outfit->{$cat}))
      ->values()
      ->all();
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'file' => $this->file,
      'likes_count' => $this->likesCount,
      'user' => $this->user,
      'used_parts' => $this->usedParts,
      'outfit_date' => $this->outfitDate,
    ];
  }
}
