<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoItemSeeder extends Seeder
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

    Item::where('user_id', $user->id)->delete();

    Item::factory()
      ->count(30)
      ->tops()
      ->for($user)
      ->create();

    Item::factory()
      ->count(24)
      ->bottoms()
      ->for($user)
      ->create();

    Item::factory()
      ->count(18)
      ->shoes()
      ->for($user)
      ->create();

    Item::factory()
      ->count(12)
      ->outer()
      ->for($user)
      ->create();

    Item::factory()
      ->count(6)
      ->accessories()
      ->for($user)
      ->create();
  }
}
