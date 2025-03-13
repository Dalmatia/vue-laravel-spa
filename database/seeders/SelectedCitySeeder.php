<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SelectedCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ユーザーが存在する場合のみ初期データを登録
        $users = User::all();

        foreach ($users as $user) {
            $exists = DB::table('selected_cities')
                ->where('user_id', $user->id)
                ->exists();
            if (!$exists) {
                DB::table('selected_cities')->insert([
                    'user_id' => $user->id,
                    'region_id' => 2, // 適宜変更
                    'prefecture_id' => 13, // 東京都
                    'city_id' => 2, // 千代田区
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
