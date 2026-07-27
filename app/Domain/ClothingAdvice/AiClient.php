<?php

namespace App\Domain\ClothingAdvice;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
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
      $response = Gemini::generativeModel("gemini-3.5-flash")
        ->withGenerationConfig(
          new GenerationConfig(
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
            responseSchema: $this->buildResponseSchema(),
          )
        )
        ->generateContent($prompt);

      $json = (array) $response->json();

      if (isset($json['items']) && is_object($json['items'])) {
        $json['items'] = (array) $json['items'];
      }

      return $json;
    } catch (\Throwable $e) {
      Log::error('Gemini JSON parse error', [
        'error' => $e->getMessage(),
      ]);

      throw $e;
    }
  }

  private function buildResponseSchema(): Schema
  {
    return new Schema(
      type: DataType::OBJECT,
      properties: [
        'summary' => new Schema(type: DataType::STRING),
        'items' => new Schema(
          type: DataType::OBJECT,
          properties: [
            'outer' => new Schema(
              type: DataType::ARRAY,
              items: new Schema(type: DataType::STRING)
            ),
            'tops' => new Schema(
              type: DataType::ARRAY,
              items: new Schema(type: DataType::STRING)
            ),
            'bottoms' => new Schema(
              type: DataType::ARRAY,
              items: new Schema(type: DataType::STRING)
            ),
            'shoes' => new Schema(
              type: DataType::ARRAY,
              items: new Schema(type: DataType::STRING)
            ),
          ],
          required: ['outer', 'tops', 'bottoms', 'shoes']
        ),
        'notes' => new Schema(
          type: DataType::ARRAY,
          items: new Schema(type: DataType::STRING),
        )
      ],
      required: ['summary', 'items', 'notes']
    );
  }
}
