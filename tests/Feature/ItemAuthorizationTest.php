<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  public function test_user_cannot_update_another_users_item(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $item = Item::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-authorization-test.webp',
      'main_category' => 1,
      'color' => 1,
    ]);

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->postJson("/api/items/{$item->id}", [
        'main_category' => 2,
        'color' => 2,
      ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('items', [
      'id' => $item->id,
      'main_category' => 1,
      'color' => 1,
    ]);
  }

  public function test_user_cannot_view_another_users_item(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $item = Item::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-authorization-test.webp',
      'main_category' => 1,
      'color' => 1,
    ]);

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->getJson("/api/items/{$item->id}");

    $response->assertForbidden();
  }

  public function test_user_cannot_delete_another_users_item(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $item = Item::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-authorization-test.webp',
      'main_category' => 1,
      'color' => 1,
    ]);

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->deleteJson("/api/items/{$item->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('items', [
      'id' => $item->id,
      'user_id' => $owner->id,
    ]);
  }
}
