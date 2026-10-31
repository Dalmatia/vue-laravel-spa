<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  private function createUser(): User
  {
    /** @var User $user */
    $user = User::factory()->create();

    return $user;
  }

  public function test_user_cannot_update_another_users_comment(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-comment-authorization.webp',
      'outfit_date' => '2026-10-29',
      'description' => 'Authorization test',
      'season' => null,
      'scene' => null,
    ]);

    $comment = new Comment();
    $comment->user_id = $owner->id;
    $comment->outfit_id = $outfit->id;
    $comment->text = 'Original comment';
    $comment->save();

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->putJson("/api/comment/{$comment->id}", [
        'text' => 'Modified comment',
      ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('comments', [
      'id' => $comment->id,
      'text' => 'Original comment',
    ]);
  }

  public function test_comment_uses_authenticated_user_id_instead_of_request_user_id(): void
  {
    \Illuminate\Support\Facades\Notification::fake();

    $owner = $this->createUser();
    $commenter = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-comment-spoof-test.webp',
      'outfit_date' => '2026-10-29',
      'description' => 'Comment spoof test',
      'season' => null,
      'scene' => null,
    ]);

    $response = $this->actingAs($commenter, 'sanctum')
      ->postJson("/api/outfit/{$outfit->id}/comment", [
        'outfit_id' => $outfit->id,
        'user_id' => $owner->id,
        'text' => 'Comment authorization test',
      ]);

    $response->assertCreated();

    $this->assertDatabaseHas('comments', [
      'outfit_id' => $outfit->id,
      'user_id' => $commenter->id,
      'text' => 'Comment authorization test',
    ]);

    $this->assertDatabaseMissing('comments', [
      'outfit_id' => $outfit->id,
      'user_id' => $owner->id,
      'text' => 'Comment authorization test',
    ]);
  }

  public function test_user_cannot_delete_another_users_comment(): void
  {
    $owner = $this->createUser();
    $anotherUser = $this->createUser();

    $outfit = Outfit::create([
      'user_id' => $owner->id,
      'file' => '/test/nonexistent-comment-delete-test.webp',
      'outfit_date' => '2026-10-29',
      'description' => 'Comment delete authorization test',
      'season' => null,
      'scene' => null,
    ]);

    $comment = new Comment();
    $comment->user_id = $owner->id;
    $comment->outfit_id = $outfit->id;
    $comment->text = 'Original comment';
    $comment->save();

    $response = $this->actingAs($anotherUser, 'sanctum')
      ->deleteJson("/api/comment/{$comment->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('comments', [
      'id' => $comment->id,
      'user_id' => $owner->id,
      'text' => 'Original comment',
    ]);
  }
}
