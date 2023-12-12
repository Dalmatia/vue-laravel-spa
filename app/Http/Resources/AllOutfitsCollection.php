<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllOutfitsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($outfit) {
            return [
                'id' => $outfit->id,
                'file' => $outfit->file,
                'description' => $outfit->description,
                'outfit_date' => $outfit->outfit_date,
                'season' => $outfit->season,
                'tops' => $outfit->tops,
                'outer' => $outfit->outer,
                'bottoms' => $outfit->bottoms,
                'shoes' => $outfit->shoes,
                'user' => [
                    'id' => $outfit->user->id,
                    'name' => $outfit->user->name,
                    'file' => $outfit->user->file
                ],
            ];
        });
    }
}
