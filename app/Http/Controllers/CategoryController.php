<?php

namespace App\Http\Controllers;

use App\Enums\MainCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getMainCategories()
    {
        return [
            'mainCategories' => MainCategory::toSelectArray(),
        ];
    }

    public function getSubCategories($mainCategory_id)
    {
        return [
            'subCategories' => MainCategory::getSubCategories($mainCategory_id),
        ];
    }
}
