<?php

namespace App\Http\Requests;

class StoreOutfitRequest extends BaseOutfitRequest
{
    public function fileRules(): array
    {
        return [
            'file' => 'required|mimes:jpg,jpeg,png'
        ];
    }
}
