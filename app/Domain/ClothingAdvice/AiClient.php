<?php

namespace App\Domain\ClothingAdvice;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class AiClient
{
  public function getClothingAdvice(string $prompt): string
  {
    try {
      $client = Gemini::generativeModel("gemini-2.0-flash-001");
      $response = $client->generateContent($prompt);
      return $response->text();
    } catch (\Exception $e) {
      Log::error("Gemini APIエラー: " . $e->getMessage());
      return 'アドバイスを取得できませんでした。';
    }
  }
}
