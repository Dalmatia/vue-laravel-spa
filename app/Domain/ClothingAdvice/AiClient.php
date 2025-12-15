<?php

namespace App\Domain\ClothingAdvice;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class AiClient
{
  public function getClothingAdvice(string $prompt): string
  {
    try {
      $client = Gemini::generativeModel("gemini-2.5-flash");
      $response = $client->generateContent($prompt);
      return $response->text();
    } catch (\Exception $e) {
      Log::error("Gemini APIエラー: " . $e->getMessage());
      return 'アドバイスを取得できませんでした。';
    }
  }

  public function getClothingAdviceJson(string $prompt): array
  {
    try {
      $client = Gemini::generativeModel("gemini-2.5-flash");
      $response = $client->generateContent($prompt);
      $text = $response->text();

      $json = $this->extractJson($text);

      return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (\Throwable $e) {
      Log::error('Gemini JSON parse error', [
        'error' => $e->getMessage(),
      ]);

      return [
        'summary' => '服装アドバイスを生成できませんでした。',
        'items' => [
          'tops' => [],
          'bottoms' => [],
          'shoes' => [],
          'outer' => [],
        ],
        'notes' => [],
      ];
    }
  }

  private function extractJson(string $text): string
  {
    $start = strpos($text, '{');
    $end = strrpos($text, '}');

    if ($start === false || $end === false) {
      return '{}';
    }

    return substr($text, $start, $end - $start + 1);
  }
}
