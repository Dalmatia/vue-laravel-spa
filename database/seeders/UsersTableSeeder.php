<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Item;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('comments')->truncate();
        DB::table('likes')->truncate();
        DB::table('outfits_items')->truncate();
        DB::table('items')->truncate();
        DB::table('outfits')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();

        // ===== 男性ユーザー =====
        $maleHeavy = User::factory()
            ->state(['gender' => Gender::Male])
            ->create();

        $maleMedium = User::factory()
            ->state(['gender' => Gender::Male])
            ->create();

        $maleNormal = User::factory()
            ->state(['gender' => Gender::Male])
            ->create();

        // ===== 女性ユーザー =====
        $femaleHeavy = User::factory()
            ->state(['gender' => Gender::Female])
            ->create();

        $femaleNormal = User::factory()
            ->state(['gender' => Gender::Female])
            ->create();

        // ===== 子供ユーザー =====    
        $kidsUser = User::factory()
            ->state(['gender' => Gender::Kids])
            ->create();

        $users = collect([
            $maleHeavy,
            $maleMedium,
            $maleNormal,
            $femaleHeavy,
            $femaleNormal,
            $kidsUser,
        ]);

        foreach ($users as $index => $user) {
            Item::factory()->count(3)->for($user)->tops($user->gender)->create();
            Item::factory()->count(3)->for($user)->bottoms($user->gender)->create();
            Item::factory()->count(2)->for($user)->shoes($user->gender)->create();
            Item::factory()->count(2)->for($user)->outer($user->gender)->create();
            Item::factory()->count(2)->for($user)->accessories($user->gender)->create();

            // ① 投稿数をユーザーごとに変える
            $count = match ($index) {
                0 => 50,
                1 => 30,
                2 => 10,
                3 => 40,
                default => 10,
            };

            // ② Outfit生成
            Outfit::factory()
                ->count($count)
                ->for($user)
                ->create();
        }
    }
}
