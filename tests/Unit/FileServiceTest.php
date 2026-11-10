<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Models\Outfit;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
  private array $createdFiles = [];

  protected function tearDown(): void
  {
    // テストで作成した画像を削除する
    foreach ($this->createdFiles as $file) {
      if (File::exists($file)) {
        File::delete($file);
      }
    }

    parent::tearDown();
  }

  // アイテム画像がWebP形式で保存されることを確認
  public function test_item_image_is_converted_to_webp(): void
  {
    $item = new Item();

    $request = $this->createImageRequest();

    $result = app(FileService::class)
      ->updateFile($item, $request, 'item');

    $this->assertStringEndsWith('.webp', $result->file);

    $savedPath = public_path() . $result->file;
    $this->createdFiles[] = $savedPath;

    $this->assertFileExists($savedPath);
    $this->assertSame('image/webp', mime_content_type($savedPath));
  }

  // コーディネート画像がWebP形式で保存されることを確認
  public function test_outfit_image_is_converted_to_webp(): void
  {
    $outfit = new Outfit();

    $request = $this->createImageRequest();

    $result = app(FileService::class)
      ->updateFile($outfit, $request, 'outfit');

    $this->assertStringEndsWith('.webp', $result->file);

    $savedPath = public_path() . $result->file;
    $this->createdFiles[] = $savedPath;

    $this->assertFileExists($savedPath);
    $this->assertSame('image/webp', mime_content_type($savedPath));
  }

  // プロフィール画像がWebP形式で保存されることを確認
  public function test_user_image_is_converted_to_webp(): void
  {
    $user = new User();

    $request = $this->createImageRequest();

    $result = app(FileService::class)
      ->updateFile($user, $request, 'user');

    $this->assertStringEndsWith('.webp', $result->file);

    $savedPath = public_path() . $result->file;
    $this->createdFiles[] = $savedPath;

    $this->assertFileExists($savedPath);
    $this->assertSame('image/webp', mime_content_type($savedPath));
  }

  // プロフィール画像が400×400にリサイズされることを確認
  public function test_user_image_is_resized_to_400_by_400(): void
  {
    $user = new User();

    $request = $this->createImageRequest(800, 600);

    $result = app(FileService::class)
      ->updateFile($user, $request, 'user');

    $savedPath = public_path() . $result->file;
    $this->createdFiles[] = $savedPath;

    $this->assertFileExists($savedPath);

    $imageSize = getimagesize($savedPath);

    $this->assertSame(400, $imageSize[0]);
    $this->assertSame(400, $imageSize[1]);
  }

  // テスト用画像を含むリクエストを作成
  private function createImageRequest(int $width = 600, int $height = 400)
  {
    $file = UploadedFile::fake()->image(
      'test-image.jpg',
      $width,
      $height
    );

    $request = new \Illuminate\Http\Request();

    // リクエストのファイル領域に画像を登録する
    $request->files->set('file', $file);

    return $request;
  }
}
