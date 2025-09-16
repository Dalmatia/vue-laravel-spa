<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KeywordMapping;

class KeywordMappingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappings = [
            ['keyword' => 'ジャケット', 'main_category' => 1, 'sub_category' => 10],
            ['keyword' => 'ブルゾン', 'main_category' => 1, 'sub_category' => 10],
            ['keyword' => 'コート', 'main_category' => 1, 'sub_category' => 11],
            ['keyword' => 'Tシャツ', 'main_category' => 2, 'sub_category' => 1],
            ['keyword' => 'シャツ', 'main_category' => 2, 'sub_category' => 2],
            ['keyword' => 'ポロシャツ', 'main_category' => 2, 'sub_category' => 3],
            ['keyword' => 'パーカー', 'main_category' => 2, 'sub_category' => 4],
            ['keyword' => 'スウェット', 'main_category' => 2, 'sub_category' => 5],
            ['keyword' => 'ニット', 'main_category' => 2, 'sub_category' => 6],
            ['keyword' => 'パンツ', 'main_category' => 3, 'sub_category' => 7],
            ['keyword' => 'デニム', 'main_category' => 3, 'sub_category' => 8],
            ['keyword' => 'スカート', 'main_category' => 3, 'sub_category' => 9],
            ['keyword' => 'スニーカー', 'main_category' => 4, 'sub_category' => 12],
            ['keyword' => '革靴', 'main_category' => 4, 'sub_category' => 13],
            ['keyword' => 'ブーツ', 'main_category' => 4, 'sub_category' => 14],
            ['keyword' => 'スーツ', 'main_category' => 1, 'sub_category' => 15],
            // 必要に応じて追加
        ];

        foreach ($mappings as $map) {
            KeywordMapping::create($map);
        }
    }
}
