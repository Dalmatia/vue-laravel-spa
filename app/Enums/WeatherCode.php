<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class WeatherCode extends Enum
{
    const Clear = 0;
    const MostlyClear = 1;
    const PartlyCloudy = 2;
    const Cloudy = 3;
    const Fog = 45;
    const FogWithRime = 48;
    const Drizzle = 51;
    const DrizzleModerate = 53;
    const DrizzleHeavy = 55;
    const RainLight = 61;
    const RainModerate = 63;
    const RainHeavy = 65;
    const SnowLight = 71;
    const SnowModerate = 73;
    const SnowHeavy = 75;
    const ShowersLight = 80;
    const ShowersModerate = 81;
    const ShowersHeavy = 82;
    const Thunderstorm = 95;
    const ThunderstormHail = 96;
    const ThunderstormSevereHail = 99;

    public static function weatherDescribe($value): string
    {
        return match ($value) {
            self::Clear => '快晴',
            self::MostlyClear => '晴れ',
            self::PartlyCloudy => '晴れ時々曇り',
            self::Cloudy => '曇り',
            self::Fog, self::FogWithRime => '霧',
            self::Drizzle, self::DrizzleModerate, self::DrizzleHeavy => '霧雨',
            self::RainLight => '小雨',
            self::RainModerate => '中程度の雨',
            self::RainHeavy => '強い雨',
            self::SnowLight => '小雪',
            self::SnowModerate => '中程度の雪',
            self::SnowHeavy => '強い雪',
            self::ShowersLight => 'にわか雨（軽）',
            self::ShowersModerate => 'にわか雨（中）',
            self::ShowersHeavy => 'にわか雨（強）',
            self::Thunderstorm => '雷雨',
            self::ThunderstormHail => '雷雨（雹あり）',
            self::ThunderstormSevereHail => '雷雨（激しい雹）',
            default => '不明',
        };
    }

    public static function weatherIcon($value): string
    {
        return match ($value) {
            self::Clear => '☀️',
            self::MostlyClear => '🌤',
            self::PartlyCloudy => '⛅',
            self::Cloudy => '☁️',
            self::Fog, self::FogWithRime => '🌫',
            self::Drizzle, self::DrizzleModerate, self::DrizzleHeavy => '🌧',
            self::RainLight => '☔',
            self::RainModerate => '☔',
            self::RainHeavy => '☔',
            self::SnowLight, self::SnowModerate, self::SnowHeavy => '❄️',
            self::ShowersLight => '🌦',
            self::ShowersModerate, self::ShowersHeavy => '🌧',
            self::Thunderstorm, self::ThunderstormHail, self::ThunderstormSevereHail => '⛈',
            default => '✨',
        };
    }
}
