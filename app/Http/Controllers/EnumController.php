<?php

namespace App\Http\Controllers;

use App\Enums\Color;
use App\Enums\Gender;
use App\Enums\MainCategory;
use App\Enums\Season;

class EnumController extends Controller
{
    public function index()
    {
        $genders = [];
        foreach (Gender::getValues() as $value) {
            $genders[] = [
                'value' => $value,
                'label' => Gender::fromValue($value)->label(),
            ];
        }

        $mainCategories = MainCategory::toSelectArray();
        $subCategories = [];
        foreach (MainCategory::getValues() as $main) {
            $subCategories[$main] = MainCategory::getSubCategories($main);
        }

        return [
            'genders' => $genders,
            'mainCategories' => $mainCategories,
            'subCategories' => $subCategories,
            'colors' => Color::toSelectArray(),
            'seasons' => Season::toSelectArray()
        ];
    }

    public function getColors()
    {
        return ['colors' => Color::toSelectArray()];
    }

    public function getSeasons()
    {
        return ['seasons' => Season::toSelectArray()];
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
