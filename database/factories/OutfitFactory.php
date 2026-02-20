<?php

namespace Database\Factories;

use App\Domain\ClothingAdvice\SeasonResolver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Outfit>
 */
class OutfitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seasonResolver = app(SeasonResolver::class);
        $todaySeason = $seasonResolver->resolve();
        $season = $this->generateWeightedSeason($todaySeason);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'description' => fake()->sentence(),
            'file' => $this->generateSeasonalImage($season),
            'outfit_date' => $this->generateNaturalDate(),
            'season' => $season,
        ];
    }

    private function generateNaturalDate()
    {
        $range = fake()->randomFloat(0, 1);

        if ($range < 0.4) {
            $date = now()->addDays(fake()->numberBetween(-7, 7));
        } elseif ($range < 0.75) {
            $date = now()->addDays(fake()->numberBetween(-30, 30));
        } elseif ($range < 0.95) {
            $date = now()->addDays(fake()->numberBetween(-90, 90));
        } else {
            $date = now()->addDays(fake()->numberBetween(-365, 365));
        }

        return $date;
    }

    private function generateWeightedSeason($todaySeason)
    {
        return fake()->randomElement(
            $this->weightedSeason($todaySeason)
        );
    }

    private function weightedSeason(int $todaySeason): array
    {
        $seasonCount = 4;

        $nextSeason = $todaySeason % $seasonCount + 1;
        $prevSeason = ($todaySeason - 2 + $seasonCount) % $seasonCount + 1;
        $oppositeSeason = ($todaySeason + 1) % $seasonCount + 1;

        return [
            ...array_fill(0, 50, $todaySeason),
            ...array_fill(0, 15, $nextSeason),
            ...array_fill(0, 10, $prevSeason),
            ...array_fill(0, 10, $oppositeSeason),
            ...array_fill(0, 15, null),
        ];
    }

    private function generateSeasonalImage(?int $season): string
    {
        return fake()->imageUrl(400, 600, 'fashion', true);
    }
}
