<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  private function createNotification(User $user, bool $read = false): DatabaseNotification
  {
    return $user->notifications()->create([
      'id' => (string) \Illuminate\Support\Str::uuid(),
      'type' => 'App\\Notifications\\TestNotification',
      'data' => [
        'message' => 'テスト通知です。',
      ],
      'read_at' => $read ? now() : null,
    ]);
  }

  public function test_user_can_get_own_notifications(): void
  {
    $user = $this->createUser();

    $this->createNotification($user);

    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/notifications/{$user->id}");

    $response->assertOk()
      ->assertJsonCount(1, 'data')
      ->assertJsonPath('data.0.message', 'テスト通知です。');
  }

  // ユーザーが他のユーザーの通知を取得できないことを確認するテスト
  public function test_user_cannot_get_another_users_notifications(): void
  {
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    $this->createNotification($anotherUser);

    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/notifications/{$anotherUser->id}");

    $response->assertForbidden();
  }

  public function test_user_can_get_own_unread_notification_count(): void
  {
    $user = $this->createUser();

    $this->createNotification($user);
    $this->createNotification($user);
    $this->createNotification($user, true);

    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/notifications/{$user->id}/unread_count");

    $response->assertOk()
      ->assertJson(['unread_count' => 2]);
  }

  public function test_user_cannot_get_another_users_unread_notification_count(): void
  {
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    $this->createNotification($anotherUser);

    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/notifications/{$anotherUser->id}/unread_count");

    $response->assertForbidden();
  }

  public function test_user_can_mark_own_notification_as_read(): void
  {
    $user = $this->createUser();

    $notification = $this->createNotification($user);

    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/notifications/{$notification->id}/read");

    $response->assertOk();

    $this->assertDatabaseMissing('notifications', [
      'id' => $notification->id,
      'read_at' => null,
    ]);
  }

  // ユーザーが他のユーザーの通知を既読にできないことを確認するテスト
  public function test_user_cannot_mark_another_users_notification_as_read(): void
  {
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    $notification = $this->createNotification($anotherUser);

    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/notifications/{$notification->id}/read");

    $response->assertNotFound();

    $this->assertDatabaseHas('notifications', [
      'id' => $notification->id,
      'notifiable_id' => $anotherUser->id,
      'read_at' => null,
    ]);
  }

  // ユーザーが自分の通知を削除できるが、他のユーザーの通知は削除できないことを確認するテスト
  public function test_user_can_delete_own_notification_but_cannot_delete_another_users_notification(): void
  {
    $user = $this->createUser();
    $anotherUser = $this->createUser();

    $ownNotification = $this->createNotification($user);
    $anotherNotification = $this->createNotification($anotherUser);

    $this->actingAs($user, 'sanctum');

    $ownResponse = $this->delete("/api/notifications/{$ownNotification->id}");

    $ownResponse->assertOk();

    $this->assertDatabaseMissing('notifications', [
      'id' => $ownNotification->id,
    ]);

    $anotherResponse = $this->delete("/api/notifications/{$anotherNotification->id}");

    $anotherResponse->assertNotFound();

    $this->assertDatabaseHas('notifications', [
      'id' => $anotherNotification->id,
      'notifiable_id' => $anotherUser->id,
    ]);
  }
}
