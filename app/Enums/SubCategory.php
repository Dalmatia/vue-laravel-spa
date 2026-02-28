<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SubCategory extends Enum
{
    // トップス
    const tShirt = 1;
    const longSleeveTee = 2;
    const shirt = 3;
    const poloShirt = 4;
    const parka = 5;
    const sweatshirt = 6;
    const knit = 7;
    const cardigan = 8;
    const vest = 9;
    const blouse = 10;
    const thermalShirt = 11;

    // ボトムス
    const pants = 12;
    const denimPants = 13;
    const slacks = 14;
    const chinoPants = 15;
    const cargoPants = 16;
    const sweatPants = 17;
    const widePants = 18;
    const shorts = 19;
    const skirt = 20;
    const pleatedSkirt = 21;
    const longSkirt = 22;

    // アウター
    const tailoredJacket = 23;
    const blazer = 24;
    const blouson = 25;
    const coat = 26;
    const trenchCoat = 27;
    const downJacket = 28;
    const windbreaker = 29;
    const cardiganJacket = 30;
    const vestOuter = 31;
    const formal_suit = 32;

    // シューズ
    const sneaker = 33;
    const leather_shoes = 34;
    const boots = 35;
    const loafers = 36;
    const sandals = 37;
    const runningShoes = 38;
    const pumps = 39;
    const heels = 40;
    const slippers = 41;

    // 小物・アクセサリー
    const cap = 42;
    const beanie = 43;
    const scarf = 44;
    const gloves = 45;
    const necktie = 46;
    const bag = 47;
    const belt = 48;

    // その他
    const other = 49;

    protected static $labels = [
        // トップス
        self::tShirt => 'Tシャツ',
        self::longSleeveTee => 'ロンT',
        self::shirt => 'シャツ',
        self::poloShirt => 'ポロシャツ',
        self::parka => 'パーカー',
        self::sweatshirt => 'スウェット',
        self::knit => 'ニット',
        self::cardigan => 'カーディガン',
        self::vest => 'ベスト',
        self::blouse => 'ブラウス',
        self::thermalShirt => 'サーマルシャツ',

        // ボトムス
        self::pants => 'パンツ',
        self::denimPants => 'デニムパンツ',
        self::slacks => 'スラックス',
        self::chinoPants => 'チノパン',
        self::cargoPants => 'カーゴパンツ',
        self::sweatPants => 'スウェットパンツ',
        self::widePants => 'ワイドパンツ',
        self::shorts => 'ショートパンツ',
        self::skirt => 'スカート',
        self::pleatedSkirt => 'プリーツスカート',
        self::longSkirt => 'ロングスカート',

        // アウター
        self::tailoredJacket => 'テーラードジャケット',
        self::blouson => 'ブルゾン',
        self::blazer => 'ブレザー',
        self::coat => 'コート',
        self::trenchCoat => 'トレンチコート',
        self::downJacket => 'ダウンジャケット',
        self::windbreaker => 'ウィンドブレーカー',
        self::cardiganJacket => 'カーディガンジャケット',
        self::vestOuter => 'ダウンベスト',
        self::formal_suit => 'フォーマルスーツ',

        // シューズ
        self::sneaker => 'スニーカー',
        self::leather_shoes => '革靴',
        self::boots => 'ブーツ',
        self::loafers => 'ローファー',
        self::sandals => 'サンダル',
        self::runningShoes => 'ランニングシューズ',
        self::pumps => 'パンプス',
        self::heels => 'ヒール',
        self::slippers => 'スリッパ',

        // 小物・アクセサリー
        self::cap => 'キャップ',
        self::beanie => 'ニット帽',
        self::scarf => 'マフラー',
        self::gloves => '手袋',
        self::necktie => 'ネクタイ',
        self::bag => 'バッグ',
        self::belt => 'ベルト',

        // その他
        self::other => 'その他',
    ];

    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しないサブカテゴリーです';
    }

    public static function getValue($key): ?int
    {
        $flipped = array_flip(static::$labels);
        return $flipped[$key] ?? null;
    }

    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::getValues() as $value) {
            $selectArray[] = [
                'id' => $value,
                'name' => static::getDescription($value),
            ];
        }

        return $selectArray;
    }
}
