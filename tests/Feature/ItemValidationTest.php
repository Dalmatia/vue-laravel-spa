<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ItemValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }

    public function test_item_creation_requires_an_image(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/items', [
                'main_category' => 1,
                'color' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_item_creation_requires_main_category(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/items', [
                'file' => UploadedFile::fake()->image('item.jpg'),
                'color' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['main_category']);
    }

    public function test_item_creation_requires_color(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/items', [
                'file' => UploadedFile::fake()->image('item.jpg'),
                'main_category' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    }

    public function test_item_can_be_updated_without_uploading_a_new_image(): void
    {
        $user = $this->createUser();

        $item = Item::create([
            'user_id' => $user->id,
            'file' => '/test/existing-item.webp',
            'main_category' => 1,
            'color' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/items/{$item->id}", [
                'main_category' => 2,
                'color' => 2,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'user_id' => $user->id,
            'file' => '/test/existing-item.webp',
            'main_category' => 2,
            'color' => 2,
        ]);
    }
}
