<?php

namespace App\Http\Requests;

class UpdateOutfitRequest extends BaseOutfitRequest
{
    public function fileRules(): array
    {
        return [
            'file' => 'sometimes|mimes:jpg,jpeg,png,webp',
        ];
    }
}
