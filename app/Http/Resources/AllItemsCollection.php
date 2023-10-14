<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllItemsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'file' => $item->file,
                'main_category' => $item->main_category,
                'sub_category' => $item->sub_category,
                'color' => $item->color,
                'season' => $item->season,
                'memo' => $item->memo,
                'created_at' => $item->created_at->format('Y M D'),
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                    'file' => $item->user->file
                ]
            ];
        });
    }
}
