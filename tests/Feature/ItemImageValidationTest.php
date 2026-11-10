<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ItemImageValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_creation_rejects_unsupported_image_format(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/items', [
                'file' => UploadedFile::fake()->create(
                    'item.gif',
                    100,
                    'image/gif'
                ),
                'main_category' => 1,
                'color' => 1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
