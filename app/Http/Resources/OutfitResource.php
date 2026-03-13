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
      'file' => asset($this->file),
      'description' => $this->description,
      'outfit_date' => $this->outfit_date,
      'items' => $this->items->map(function ($item) {
        return [
          'id' => $item->id,
          'file' => asset($item->file),
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
