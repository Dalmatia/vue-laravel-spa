<?php

namespace App\Http\Controllers;

use App\Enums\Color;
use App\Enums\Gender;
use App\Enums\MainCategory;
use App\Enums\Season;
use App\Enums\SubCategory;
use Illuminate\Http\Request;

class EnumController extends Controller
{
    public function index()
    {
        return [
            'mainCategories' => MainCategory::toSelectArray(),
            'subCategories' => SubCategory::toSelectArray(),
            'colors' => Color::toSelectArray(),
            'seasons' => Season::toSelectArray()
        ];
    }

    public function getGenders()
    {
        $options = [];
        foreach (Gender::getValues() as $value) {
            $options[] = [
                'value' => $value,
                'label' => Gender::fromValue($value)->label(),
            ];
        }
        return response()->json($options);
    }
}
