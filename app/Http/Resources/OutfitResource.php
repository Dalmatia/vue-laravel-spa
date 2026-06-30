<?php

namespace App\Http\Resources;

use App\Enums\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   */
  public function toArray(Request $request)
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'file' => asset($this->file),
      'description' => $this->description,
      'outfit_date' => $this->outfit_date?->format('Y-m-d'),
      'season' => $this->season,
      'scene' => $this->scene,
      'items' => $this->items->map(function ($item) {
        return [
          'id' => $item->id,
          'role' => $item->pivot->role,
          'file' => asset($item->file),
          'main_category' => $item->main_category,
          'sub_category' => $item->sub_category,
          'color' => $item->color,
          'season' => $item->season,
          'sub_category_name' => SubCategory::getDescription($item->sub_category),
        ];
      }),
      'likes_count' => $this->likes_count,
      'comments_count' => $this->comments_count,
      'is_liked' => (bool) $this->is_liked,
      'user' => [
        'id' => $this->user->id,
        'name' => $this->user->name,
        'file' => $this->user->file
      ],
    ];
  }
}
