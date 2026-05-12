<?php

namespace Database\Seeders;

use App\Models\Outfit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoOutfitSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where(
            'email',
            'sample@email.com',
        )->first();

        if (!$user) {
            return;
        }

        Outfit::factory()
            ->count(120)
            ->for($user)
            ->create();
    }
}
