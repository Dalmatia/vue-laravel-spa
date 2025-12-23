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

      $jsonText = $this->extractJson($text);
      $json = json_decode($jsonText, true, 512, JSON_THROW_ON_ERROR);

      if (!isset($json['items']) || !is_array($json['items'])) {
        throw new \RuntimeException('Invalid AI JSON structure');
      }

      return $json;
    } catch (\Throwable $e) {
      Log::error('Gemini JSON parse error', [
        'error' => $e->getMessage(),
      ]);

      throw $e;
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
