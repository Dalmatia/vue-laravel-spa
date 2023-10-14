import { defineStore } from 'pinia';

export const getEnumStore = defineStore('enum', {
    state: () => ({
        mainCategory: {
            1: 'アウター',
            2: 'トップス',
            3: 'ボトムス',
            4: 'シューズ',
            5: 'アクセサリー',
        },
        subCategory: {
            1: 'Tシャツ',
            2: 'シャツ',
            3: 'ポロシャツ',
            4: 'パーカー',
            5: 'スウェット',
            6: 'ニット',
            7: 'パンツ',
            8: 'デニムパンツ',
            9: 'スカート',
            10: 'ジャケット/ブルゾン',
            11: 'コート',
            12: 'スニーカー',
            13: '革靴',
            14: 'ブーツ',
            15: 'フォーマルスーツ',
            16: 'その他',
        },
        itemColor: {
            1: 'ブラック',
            2: 'ホワイト',
            3: 'グレー',
            4: 'レッド',
            5: 'ネイビー',
            6: 'ブルー',
            7: 'ライトブルー',
            8: 'グリーン',
            9: 'オリーブ',
            10: 'ブラウン',
            11: 'ベージュ',
            12: 'パープル',
            13: 'イエロー',
            14: 'オレンジ',
            15: 'ピンク',
            16: 'ネオン',
            17: 'ボーダー柄',
            18: 'パターン柄',
            19: 'デニム',
            20: 'シルバー',
            21: 'ゴールド',
            22: 'その他',
        },
        itemSeason: {
            1: '春',
            2: '夏',
            3: '秋',
            4: '冬',
        },
    }),
    getters: {
        getMainCategoryName: (state) => (main_category) => {
            return state.mainCategory[main_category] || '指定なし';
        },
        getSubCategoryName: (state) => (sub_category) => {
            return state.subCategory[sub_category] || '指定なし';
        },
        getColor: (state) => (color) => {
            return state.itemColor[color] || '指定なし';
        },
        getSeason: (state) => (season) => {
            return state.itemSeason[season] || '指定なし';
        },
    },
});
