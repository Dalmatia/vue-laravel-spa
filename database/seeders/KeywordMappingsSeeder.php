<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KeywordMapping;
use App\Enums\MainCategory;
use App\Enums\SubCategory;

class KeywordMappingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KeywordMapping::truncate();

        $mappings = [
            // ===== トップス =====
            ['keyword' => 'Tシャツ', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::tShirt],
            ['keyword' => 'ロンT', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::longSleeveTee],
            ['keyword' => '長袖Tシャツ', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::longSleeveTee],
            ['keyword' => 'シャツ', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::shirt],
            ['keyword' => 'ポロシャツ', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::poloShirt],
            ['keyword' => 'パーカー', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::parka],
            ['keyword' => 'スウェット', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::sweatshirt],
            ['keyword' => 'ニット', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::knit],
            ['keyword' => 'セーター', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::knit],
            ['keyword' => 'カーディガン', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::cardigan],
            ['keyword' => 'ベスト', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::vest],
            ['keyword' => 'ブラウス', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::blouse],
            ['keyword' => 'サーマルシャツ', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::thermalShirt],

            // ===== ボトムス =====
            ['keyword' => 'パンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::pants],
            ['keyword' => 'ズボン', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::pants],
            ['keyword' => 'デニム', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::denimPants],
            ['keyword' => 'ジーンズ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::denimPants],
            ['keyword' => 'スラックス', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::slacks],
            ['keyword' => 'チノパン', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::chinoPants],
            ['keyword' => 'カーゴパンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::cargoPants],
            ['keyword' => 'スウェットパンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::sweatPants],
            ['keyword' => 'ワイドパンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::widePants],
            ['keyword' => 'ショートパンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::shorts],
            ['keyword' => 'ハーフパンツ', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::shorts],
            ['keyword' => 'スカート', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::skirt],
            ['keyword' => 'プリーツスカート', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::pleatedSkirt],
            ['keyword' => 'ロングスカート', 'main_category' => MainCategory::bottoms, 'sub_category' => SubCategory::longSkirt],

            // ===== アウター =====
            ['keyword' => 'ジャケット', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::tailoredJacket],
            ['keyword' => 'テーラードジャケット', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::tailoredJacket],
            ['keyword' => 'ブレザー', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::blazer],
            ['keyword' => 'ブルゾン', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::blouson],
            ['keyword' => 'コート', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::coat],
            ['keyword' => 'トレンチコート', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::trenchCoat],
            ['keyword' => 'ダウンジャケット', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::downJacket],
            ['keyword' => 'ウィンドブレーカー', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::windbreaker],
            ['keyword' => 'カーディガンジャケット', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::cardiganJacket],
            ['keyword' => 'ダウンベスト', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::vestOuter],
            ['keyword' => 'フォーマルスーツ', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::formal_suit],
            ['keyword' => 'スーツ', 'main_category' => MainCategory::outer, 'sub_category' => SubCategory::formal_suit],

            // ===== シューズ =====
            ['keyword' => 'スニーカー', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::sneaker],
            ['keyword' => '革靴', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::leather_shoes],
            ['keyword' => 'ブーツ', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::boots],
            ['keyword' => 'ローファー', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::loafers],
            ['keyword' => 'サンダル', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::sandals],
            ['keyword' => 'ランニングシューズ', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::runningShoes],
            ['keyword' => 'パンプス', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::pumps],
            ['keyword' => 'ヒール', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::heels],
            ['keyword' => 'スリッパ', 'main_category' => MainCategory::shoes, 'sub_category' => SubCategory::slippers],

            // ===== 小物・アクセサリー =====
            ['keyword' => 'キャップ', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::cap],
            ['keyword' => '帽子', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::cap],
            ['keyword' => 'ニット帽', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::beanie],
            ['keyword' => 'マフラー', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::scarf],
            ['keyword' => 'スカーフ', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::scarf],
            ['keyword' => '手袋', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::gloves],
            ['keyword' => 'ネクタイ', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::necktie],
            ['keyword' => 'バッグ', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::bag],
            ['keyword' => 'ベルト', 'main_category' => MainCategory::accessories, 'sub_category' => SubCategory::belt],

            // ===== その他 =====
            ['keyword' => 'その他', 'main_category' => MainCategory::tops, 'sub_category' => SubCategory::other],
        ];

        foreach ($mappings as $map) {
            KeywordMapping::create($map);
        }
    }
}
