<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Outfit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\Scene;

abstract class BaseOutfitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge($this->baseRules(), $this->fileRules());
    }

    protected function baseRules(): array
    {
        return [
            'description' => 'nullable|max:1000',
            'outfit_date' => 'required',
            'season' => 'nullable',
            'scene' => ['nullable', 'integer', Rule::in(Scene::getValues())],
            'items' => 'nullable|json',
        ];
    }

    abstract protected function fileRules(): array;

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $exists = Outfit::where('outfit_date', $this->outfit_date)
                ->where('user_id', Auth::id())
                ->when($this->route('id'), function ($query) {
                    $query->where('id', '!=', $this->route('id'));
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('outfit_date', '同じ日付に複数の投稿はできません。');
            }
        });
    }
}
