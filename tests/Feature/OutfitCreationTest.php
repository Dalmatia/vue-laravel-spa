<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OutfitCreationTest extends TestCase
{
  use RefreshDatabase;

  private array $createdFiles = [];

  // テスト後に作成した画像を削除する
  protected function tearDown(): void
  {
    foreach ($this->createdFiles as $file) {
      if (file_exists($file)) {
        unlink($file);
      }
    }

    parent::tearDown();
  }

  public function test_user_can_create_outfit_with_image_and_item_relations(): void
  {
    /** @var User $user */
    // ユーザーを作成
    $user = User::factory()->create();

    // ユーザーが所有するトップスとボトムスを作成
    $tops = Item::factory()
      ->for($user)
      ->tops()
      ->create();

    $bottoms = Item::factory()
      ->for($user)
      ->bottoms()
      ->create();

    // 画像付きでコーデ投稿を送信
    $response = $this->actingAs($user, 'sanctum')
      ->post('/api/outfit', [
        'file' => UploadedFile::fake()->image(
          'outfit.jpg',
          600,
          400
        ),
        'description' => 'Test outfit',
        'outfit_date' => '2026-11-10',
        'season' => 3,
        'scene' => 1,
        'items' => json_encode([
          [
            'item_id' => $tops->id,
            'role' => 2,
          ],
          [
            'item_id' => $bottoms->id,
            'role' => 3,
          ],
        ]),
      ]);

    // 投稿が正常に作成されたことを確認
    $response->assertOk();

    // データベースから投稿を取得
    $outfit = Outfit::where('user_id', $user->id)
      ->whereDate('outfit_date', '2026-11-10')
      ->firstOrFail();

    // テストで生成した画像を後処理対象に登録
    $imagePath = public_path($outfit->file);
    $this->createdFiles[] = $imagePath;

    // 投稿情報が保存されていることを確認
    $this->assertDatabaseHas('outfits', [
      'id' => $outfit->id,
      'user_id' => $user->id,
      'description' => 'Test outfit',
      'outfit_date' => '2026-11-10',
      'season' => 3,
      'scene' => 1,
    ]);

    // 画像ファイルがWebP形式で保存されていることを確認
    $this->assertFileExists($imagePath);
    $this->assertStringEndsWith('.webp', $outfit->file);
    $this->assertSame('image/webp', mime_content_type($imagePath));

    // 中間テーブルに正しいアイテムとroleが保存されていることを確認
    $this->assertDatabaseHas('outfits_items', [
      'outfit_id' => $outfit->id,
      'item_id' => $tops->id,
      'role' => 2,
    ]);

    $this->assertDatabaseHas('outfits_items', [
      'outfit_id' => $outfit->id,
      'item_id' => $bottoms->id,
      'role' => 3,
    ]);

    // Eloquentリレーションからも正しく取得できることを確認
    $savedItems = $outfit->fresh()->items;

    $this->assertCount(2, $savedItems);

    $this->assertSame(
      2,
      (int) $savedItems->firstWhere('id', $tops->id)->pivot->role
    );

    $this->assertSame(
      3,
      (int) $savedItems->firstWhere('id', $bottoms->id)->pivot->role
    );
  }
}
