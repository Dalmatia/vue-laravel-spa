<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'nullable|mimes:jpg,jpeg,png,webp',
            'main_category' => 'required',
            'sub_category' => 'nullable',
            'color' => 'required',
            'season' => 'nullable',
            'memo' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'ファイル形式は jpg、jpeg、png、webp のいずれかを選択してください。',
            'main_category.required' => 'メインカテゴリーを選択してください。',
            'color.required' => 'カラーを選択してください。',
        ];
    }
}
