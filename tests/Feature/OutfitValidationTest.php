<?php

namespace Tests\Feature;

use App\Models\Outfit;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OutfitValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }

    public function test_outfit_creation_requires_an_image(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_outfit_creation_requires_outfit_date(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outfit_date']);
    }

    public function test_outfit_creation_rejects_invalid_scene(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
                'scene' => 99999,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['scene']);
    }

    public function test_outfit_creation_rejects_invalid_items_json(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
                'items' => 'not-json',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    // 許可したファイル形式以外の画像をアップロードしようとした場合にバリデーションエラーになることを確認するテスト
    public function test_outfit_creation_rejects_unsupported_image_format(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'file' => UploadedFile::fake()->create(
                    'outfit.gif',
                    100,
                    'image/gif'
                ),
                'outfit_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    // 同じ日に同じユーザーが複数のコーディネートを投稿できないことを確認するテスト
    public function test_outfit_creation_rejects_duplicate_date_for_same_user(): void
    {
        $user = $this->createUser();

        Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outfit_date']);
    }

    // 同じ日に別のユーザーがコーディネートを投稿できることを確認するテスト
    public function test_outfit_creation_allows_same_date_for_different_users(): void
    {
        $existingUser = $this->createUser();
        $postingUser = $this->createUser();

        Outfit::create([
            'user_id' => $existingUser->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Another user outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($postingUser, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['outfit_date'])
            ->assertJsonValidationErrors(['file']);
    }

    // 更新時に画像をアップロードせずに他の項目を更新できることを確認するテスト
    public function test_outfit_can_be_updated_without_uploading_a_new_image(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Before update',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'outfit_date' => '2026-10-31',
                'description' => 'After update',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('outfits', [
            'id' => $outfit->id,
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'description' => 'After update',
        ]);
    }

    // 更新時に許可したファイル形式以外の画像をアップロードしようとした場合にバリデーションエラーになることを確認するテスト
    public function test_outfit_update_rejects_unsupported_image_format(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'file' => UploadedFile::fake()->create(
                    'outfit.gif',
                    100,
                    'image/gif'
                ),
                'outfit_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_outfit_creation_rejects_invalid_date_format(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026/10/31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outfit_date']);
    }

    public function test_outfit_creation_rejects_invalid_season(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
                'season' => 5,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['season']);
    }

    public function test_outfit_creation_allows_null_season(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
                'season' => null,
            ]);

        $response->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['season'])
            ->assertJsonValidationErrors(['file']);
    }

    public function test_outfit_creation_rejects_description_over_1000_characters(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-10-31',
                'description' => str_repeat('あ', 1001),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    // 更新時に不正な日付形式を拒否することを確認するテスト
    public function test_outfit_update_rejects_invalid_date_format(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'outfit_date' => '2026/10/31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outfit_date']);
    }

    // 更新時に定義外のSeasonを拒否することを確認するテスト
    public function test_outfit_update_rejects_invalid_season(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'outfit_date' => '2026-10-31',
                'season' => 5,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['season']);
    }

    // 更新時にDescriptionが1000文字を超える場合に拒否することを確認するテスト
    public function test_outfit_update_rejects_description_over_1000_characters(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'outfit_date' => '2026-10-31',
                'description' => str_repeat('あ', 1001),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    // 更新時に同じユーザーの別の投稿と日付が重複する場合に拒否することを確認するテスト
    public function test_outfit_update_rejects_duplicate_date_for_same_user(): void
    {
        $user = $this->createUser();

        $outfit = Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/existing-outfit.webp',
            'outfit_date' => '2026-10-31',
            'description' => 'Existing outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        Outfit::create([
            'user_id' => $user->id,
            'file' => '/test/another-outfit.webp',
            'outfit_date' => '2026-11-01',
            'description' => 'Another outfit',
            'season' => 3,
            'scene' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/outfit/{$outfit->id}", [
                'outfit_date' => '2026-11-01',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['outfit_date']);
    }

    // 正しいアイテム情報がバリデーションを通過することを確認するテスト
    public function test_outfit_creation_accepts_valid_items(): void
    {
        $user = $this->createUser();

        $item = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $item->id,
                        'role' => 2,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['items'])
            ->assertJsonValidationErrors(['file']);
    }

    // 存在しないアイテムIDを拒否するテスト
    public function test_outfit_creation_rejects_nonexistent_item_id(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => 999999,
                        'role' => 2,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    // 他ユーザーが所有するアイテムの使用を拒否するテスト
    public function test_outfit_creation_rejects_another_users_item(): void
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();

        $item = Item::factory()
            ->for($anotherUser)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $item->id,
                        'role' => 2,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.item_id']);
    }

    // 定義されていないroleを拒否するテスト
    public function test_outfit_creation_rejects_invalid_item_role(): void
    {
        $user = $this->createUser();

        $item = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $item->id,
                        'role' => 999,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    // roleとアイテムのカテゴリが一致しない場合に拒否するテスト
    public function test_outfit_creation_rejects_mismatched_item_category_and_role(): void
    {
        $user = $this->createUser();

        $item = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $item->id,
                        'role' => 3,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.role']);
    }

    // 同じroleを複数指定することを拒否するテスト
    public function test_outfit_creation_rejects_duplicate_item_roles(): void
    {
        $user = $this->createUser();

        $firstItem = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $secondItem = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $firstItem->id,
                        'role' => 2,
                    ],
                    [
                        'item_id' => $secondItem->id,
                        'role' => 2,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    // 同じアイテムを複数指定することを拒否するテスト
    public function test_outfit_creation_rejects_duplicate_item_ids(): void
    {
        $user = $this->createUser();

        $item = Item::factory()
            ->for($user)
            ->tops()
            ->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/outfit', [
                'outfit_date' => '2026-11-02',
                'items' => json_encode([
                    [
                        'item_id' => $item->id,
                        'role' => 2,
                    ],
                    [
                        'item_id' => $item->id,
                        'role' => 5,
                    ],
                ]),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }
}
