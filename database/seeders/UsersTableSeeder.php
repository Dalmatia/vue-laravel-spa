<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('comments')->truncate();
        DB::table('likes')->truncate();
        DB::table('outfits_items')->truncate();
        Outfit::truncate();

        Schema::enableForeignKeyConstraints();

        // 既存ユーザーを取得
        $existingUsers = User::count();

        // もし0人ならテストユーザーを作る
        if ($existingUsers === 0) {
            // 男性ユーザー
            $maleHeavy = User::factory()
                ->state(['gender' => Gender::Male])
                ->create();

            $maleMedium = User::factory()
                ->state(['gender' => Gender::Male])
                ->create();

            $maleNormal = User::factory()
                ->count(1)
                ->state(['gender' => Gender::Male])
                ->create();

            // 女性ユーザー
            $femaleHeavy = User::factory()
                ->state(['gender' => Gender::Female])
                ->create();

            $femaleNormal = User::factory()
                ->count(1)
                ->state(['gender' => Gender::Female])
                ->create();

            $kidsUser = User::factory()
                ->state(['gender' => Gender::Kids])
                ->create();

            $users = collect([
                $maleHeavy,
                $maleMedium,
                $maleNormal->first(),
                $femaleHeavy,
                $femaleNormal->first(),
                $kidsUser,
            ]);
        } else {
            $users = User::take(5)->get();
        }

        foreach ($users as $index => $user) {
            $count = match ($index) {
                0 => 50, // ヘビーユーザー
                1 => 30,
                2 => 10,
                3 => 40,
                default => 10,
            };

            Outfit::factory()
                ->count($count)
                ->for($user)
                ->create();
        }
    }
}
