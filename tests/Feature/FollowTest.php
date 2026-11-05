<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use App\Notifications\FollowedUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FollowTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  public function test_user_can_follow_another_user(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $targetUser = $this->createUser();

    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/follow/{$targetUser->id}");

    $response->assertOk()
      ->assertJson(['success' => true]);

    $this->assertDatabaseHas('follows', [
      'following_id' => $user->id,
      'followed_id' => $targetUser->id,
    ]);
  }

  public function test_follow_status_returns_true_for_followed_user(): void
  {
    $user = $this->createUser();
    $targetUser = $this->createUser();

    Follow::create([
      'following_id' => $user->id,
      'followed_id' => $targetUser->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/follow/status/{$targetUser->id}");

    $response->assertOk()
      ->assertJson(['is_followed' => true]);
  }

  public function test_user_can_unfollow_another_user(): void
  {
    $user = $this->createUser();
    $targetUser = $this->createUser();

    Follow::create([
      'following_id' => $user->id,
      'followed_id' => $targetUser->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
      ->delete("/api/follow/{$targetUser->id}");

    $response->assertOk()
      ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('follows', [
      'following_id' => $user->id,
      'followed_id' => $targetUser->id,
    ]);
  }

  public function test_user_cannot_follow_themselves(): void
  {
    $user = $this->createUser();

    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/follow/{$user->id}");

    $response->assertStatus(400)
      ->assertJson([
        'error' => 'ユーザー自身をフォローすることはできません',
      ]);

    $this->assertDatabaseMissing('follows', [
      'following_id' => $user->id,
      'followed_id' => $user->id,
    ]);
  }

  public function test_user_cannot_unfollow_themselves(): void
  {
    $user = $this->createUser();

    $response = $this->actingAs($user, 'sanctum')
      ->delete("/api/follow/{$user->id}");

    $response->assertStatus(400)
      ->assertJson([
        'error' => 'ユーザー自身のフォロー解除は出来ません',
      ]);
  }

  // ユーザーが同じユーザーを2回フォローしようとした場合、2回目のフォローは無視されることを確認するテスト
  public function test_user_cannot_follow_same_user_twice(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $targetUser = $this->createUser();

    $this->actingAs($user, 'sanctum');

    $firstResponse = $this->post("/api/follow/{$targetUser->id}");

    $firstResponse->assertOk()
      ->assertJson(['success' => true]);

    $secondResponse = $this->post("/api/follow/{$targetUser->id}");

    $secondResponse->assertOk()
      ->assertJson(['success' => false]);

    $this->assertDatabaseCount('follows', 1);
  }

  public function test_following_user_sends_notification(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $targetUser = $this->createUser();

    $this->actingAs($user, 'sanctum')
      ->post("/api/follow/{$targetUser->id}")
      ->assertOk();

    Notification::assertSentTo(
      $targetUser,
      FollowedUser::class
    );
  }

  // ユーザーが同じユーザーを2回フォローしようとした場合、通知は1回しか送信されないことを確認するテスト
  public function test_duplicate_follow_does_not_send_duplicate_notification(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $targetUser = $this->createUser();

    $this->actingAs($user, 'sanctum');

    $this->post("/api/follow/{$targetUser->id}")->assertOk();
    $this->post("/api/follow/{$targetUser->id}")->assertOk();

    Notification::assertSentTo(
      $targetUser,
      FollowedUser::class,
      1
    );

    $this->assertDatabaseCount('follows', 1);
  }
}
