<?php

namespace Database\Factories;

use App\Domain\ClothingAdvice\SeasonResolver;
use App\Enums\Gender;
use App\Enums\Season;
use App\Models\Item;
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
        $user = User::inRandomOrder()->first();
        $seasonResolver = app(SeasonResolver::class);
        $date = $this->generateNaturalDate();
        $season = $seasonResolver->resolve($date->toDateString());

        return [
            'user_id' => $user->id,
            'description' => fake()->sentence(),
            'file' => $this->generateSeasonalImage($season, $user->gender),
            'outfit_date' => $date,
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

    private function generateSeasonalImage(?int $season, Gender $gender): string
    {
        $seasonMap = [
            Season::spring => 'spring',
            Season::summer => 'summer',
            Season::fall => 'fall',
            Season::winter => 'winter',
        ];

        $genderMap = [
            Gender::Male => 'mens',
            Gender::Female => 'womens',
            Gender::Kids => 'kids',
        ];

        $seasonFolder = $seasonMap[$season] ?? 'spring';
        $genderFolder = $genderMap[$gender->value] ?? 'mens';

        $files = glob(public_path("dummy/outfits/{$genderFolder}/{$seasonFolder}/*.jpg"));

        if (empty($files)) {
            return "dummy/outfits/mens/spring/mens_spring1.jpg";
        }

        $file = basename($files[array_rand($files)]);

        return "/dummy/outfits/{$genderFolder}/{$seasonFolder}/{$file}";
    }

    public function configure()
    {
        return $this->afterCreating(function ($outfit) {

            $items = Item::where('user_id', $outfit->user_id)
                ->inRandomOrder()
                ->limit(rand(2, 4))
                ->get();

            if ($items->isNotEmpty()) {
                $outfit->items()->attach($items->pluck('id')->toArray());
            }
        });
    }
}
