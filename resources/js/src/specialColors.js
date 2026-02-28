export const specialColors = () => {
    const specialColorNames = [
        'ネオン',
        'ボーダー柄',
        'パターン柄',
        'シルバー',
        'ゴールド',
        'その他',
    ];

    // クラスで特殊色のスタイルを分岐
    const getColorClass = (color) => {
        if (color.name === 'ネオン') {
            return 'neon-glow';
        } else if (color.name === 'ボーダー柄') {
            return 'border-pattern';
        } else if (color.name === 'パターン柄') {
            return 'patterned-pattern';
        } else if (color.name === 'シルバー') {
            return 'silver';
        } else if (color.name === 'ゴールド') {
            return 'gold';
        } else if (color.name === 'その他') {
            return 'other-color';
        } else {
            return '';
        }
    };

    // 通常色は直接背景色で表現
    const getColorStyle = (color) => {
        return specialColorNames.includes(color?.name)
            ? {}
            : { backgroundColor: color.hex };
    };

    return { getColorClass, getColorStyle };
};
