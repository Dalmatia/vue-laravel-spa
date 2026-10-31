<?php

namespace Tests\Feature;

use App\Models\Outfit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutfitAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  public function test_user_cannot_update_another_users_outfit(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-outfit-authorization-test.webp',
      'outfit_date' => '2026-10-29',
      'description' => 'Original description',
      'season' => null,
      'scene' => null,
    ]);

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->postJson("/api/outfit/{$outfit->id}", [
        'outfit_date' => '2026-10-29',
        'description' => 'Changed description',
      ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('outfits', [
      'id' => $outfit->id,
      'user_id' => $owner->id,
      'outfit_date' => '2026-10-29',
      'description' => 'Original description',
    ]);
  }

  public function test_user_cannot_delete_another_users_outfit(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-authorization-test.webp',
      'outfit_date' => '2026-10-29',
      'description' => 'Original description',
      'season' => null,
      'scene' => null,
    ]);

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->deleteJson("/api/outfit/{$outfit->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('outfits', [
      'id' => $outfit->id,
      'user_id' => $owner->id,
      'description' => 'Original description',
    ]);
  }
}
