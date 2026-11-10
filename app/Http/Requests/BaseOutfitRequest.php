<?php

namespace App\Http\Requests;

use App\Enums\OutfitRole;
use App\Enums\Scene;
use App\Enums\Season;
use App\Models\Item;
use App\Models\Outfit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;


abstract class BaseOutfitRequest extends FormRequest
{
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
            'description' => 'nullable|string|max:1000',
            'outfit_date' => 'required|date_format:Y-m-d',
            'season' => ['nullable', 'integer', Rule::in(Season::getValues())],
            'scene' => ['nullable', 'integer', Rule::in(Scene::getValues())],
            'items' => 'nullable|json',
        ];
    }

    abstract protected function fileRules(): array;

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateOutfitDate($validator);
            $this->validateItems($validator);
        });
    }

    // 同じ日に重複する投稿がないかの確認
    private function validateOutfitDate(Validator $validator): void
    {
        if ($validator->errors()->has('outfit_date')) {
            return;
        }

        $exists = Outfit::where('outfit_date', $this->outfit_date)
            ->where('user_id', Auth::id())
            ->when($this->route('id'), function ($query) {
                $query->where('id', '!=', $this->route('id'));
            })
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'outfit_date',
                '同じ日付に複数の投稿はできません。'
            );
        }
    }

    private function validateItems(Validator $validator): void
    {
        if ($validator->errors()->has('items')) {
            return;
        }

        $rawItems = $this->input('items');

        // items が省略または null の場合は、ここでは検証しない
        if ($rawItems === null) {
            return;
        }

        $items = json_decode($rawItems, true);

        // JSONとして正しくても、配列形式でなければ受け付けない
        if (!is_array($items) || !array_is_list($items)) {
            $validator->errors()->add(
                'items',
                'アイテム情報の形式が正しくありません。'
            );
            return;
        }

        // 配列内の各アイテムの基本的な形式を検証
        $itemValidator = ValidatorFacade::make($items, [
            '*' => ['required', 'array'],
            '*.item_id' => ['required', 'integer', 'distinct', 'exists:items,id'],
            '*.role' => ['required', 'integer', Rule::in(OutfitRole::getValues())],
        ]);

        if ($itemValidator->fails()) {
            foreach ($itemValidator->errors()->all() as $message) {
                $validator->errors()->add('items', $message);
            }
            return;
        }

        // 同じ役割が複数指定されていないか確認
        $roles = array_column($items, 'role');

        if (count($roles) !== count(array_unique($roles))) {
            $validator->errors()->add(
                'items',
                '同じ役割に複数のアイテムは指定できません。'
            );
            return;
        }

        // アイテムの所有者と役割・カテゴリの整合性を確認
        $categoryByRole = [
            OutfitRole::outer => 1,
            OutfitRole::tops => 2,
            OutfitRole::bottoms => 3,
            OutfitRole::shoes => 4,
            OutfitRole::inner => 2,
        ];

        foreach ($items as $index => $itemData) {
            $item = Item::find($itemData['item_id']);

            // 存在しないアイテム、または他ユーザーのアイテムを拒否
            if (!$item || (int) $item->user_id !== (int) Auth::id()) {
                $validator->errors()->add(
                    "items.{$index}.item_id",
                    '指定されたアイテムを使用できません。'
                );
                continue;
            }

            // role と main_category の対応を確認
            $expectedCategory = $categoryByRole[$itemData['role']];

            if ((int) $item->main_category !== $expectedCategory) {
                $validator->errors()->add(
                    "items.{$index}.role",
                    'アイテムのカテゴリと役割が一致していません。'
                );
            }
        }
    }
}
