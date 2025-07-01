<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|mimes:jpg,jpeg,png',
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
            'file.required' => '登録アイテムを選択してください。',
            'file.mimes' => 'ファイル形式は jpg、jpeg、png のいずれかを選択してください。',
            'main_category.required' => 'メインカテゴリーを選択してください。',
            'color.required' => 'カラーを選択してください。',
        ];
    }
}
