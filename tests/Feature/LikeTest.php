<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Outfit;
use App\Models\User;
use App\Notifications\OutfitLiked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LikeTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  public function test_user_can_like_outfit(): void
  {
    Notification::fake();

    // ユーザーと投稿を作成
    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // いいねを送信
    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/outfit/{$outfit->id}/like");

    // レスポンスとDBを確認
    $response->assertOk()
      ->assertJson(['count' => 1]);

    $this->assertDatabaseHas('likes', [
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 1,
    ]);
  }

  public function test_user_can_unlike_outfit(): void
  {
    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // 事前にいいねを登録
    Like::create([
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 1,
    ]);

    // いいね解除
    $response = $this->actingAs($user, 'sanctum')
      ->delete("/api/outfit/{$outfit->id}/unlike");

    $response->assertOk()
      ->assertJson(['count' => 0]);

    $this->assertDatabaseHas('likes', [
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 0,
    ]);
  }

  public function test_liking_another_users_outfit_sends_notification(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    $this->actingAs($user, 'sanctum')
      ->post("/api/outfit/{$outfit->id}/like")
      ->assertOk();

    Notification::assertSentTo(
      $postUser,
      OutfitLiked::class
    );
  }

  public function test_liking_own_outfit_does_not_send_notification(): void
  {
    Notification::fake();

    $user = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $user->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    $this->actingAs($user, 'sanctum')
      ->post("/api/outfit/{$outfit->id}/like")
      ->assertOk();

    Notification::assertNothingSent();
  }

  // いいねをしたことがないユーザーが初めていいねをする場合、未いいね状態のレコードを作成する
  public function test_firstcheck_creates_unliked_record_for_new_user(): void
  {
    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // 初回のいいね状態を取得
    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/outfit/{$outfit->id}/firstcheck");

    $response->assertOk()
      ->assertExactJson([0, 0]);

    // 未いいね状態のレコードが作成されたことを確認
    $this->assertDatabaseHas('likes', [
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 0,
    ]);
  }

  public function test_firstcheck_returns_existing_like_status_and_count(): void
  {
    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // 事前にいいねを登録
    Like::create([
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 1,
    ]);

    // いいね状態を取得
    $response = $this->actingAs($user, 'sanctum')
      ->get("/api/outfit/{$outfit->id}/firstcheck");

    $response->assertOk()
      ->assertExactJson([1, 1]);
  }

  // 再度いいねをした際に、通知が再送信されるかを確認するテスト
  public function test_liking_already_liked_outfit_sends_notification_again(): void
  {
    Notification::fake();

    $user = $this->createUser();
    $postUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    $this->actingAs($user, 'sanctum');

    // 1回目のいいね
    $this->post("/api/outfit/{$outfit->id}/like")
      ->assertOk();

    // 2回目のいいね
    $this->post("/api/outfit/{$outfit->id}/like")
      ->assertOk();

    // 同じ投稿者に通知が2回送信されることを確認
    Notification::assertSentTo(
      $postUser,
      OutfitLiked::class,
      2
    );

    // いいねレコード自体は重複していないことを確認
    $this->assertDatabaseCount('likes', 1);
  }

  // 通知OFFなら、いいねをされても通知が送信されないことを確認するテスト
  public function test_like_notification_is_not_created_when_setting_is_disabled(): void
  {
    $user = $this->createUser();
    $postUser = $this->createUser();

    // 投稿者のいいね通知設定をOFFにする
    $postUser->setting()->update([
      'inapp_like' => false,
    ]);

    $outfit = Outfit::create([
      'user_id' => $postUser->id,
      'file' => 'file/outfit/test.webp',
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // いいねを送信
    $response = $this->actingAs($user, 'sanctum')
      ->post("/api/outfit/{$outfit->id}/like");

    $response->assertOk()
      ->assertJson(['count' => 1]);

    // いいね自体は登録されている
    $this->assertDatabaseHas('likes', [
      'user_id' => $user->id,
      'outfit_id' => $outfit->id,
      'like' => 1,
    ]);

    // データベース通知は作成されない
    $this->assertDatabaseMissing('notifications', [
      'notifiable_id' => $postUser->id,
      'notifiable_type' => User::class,
    ]);
  }
}
