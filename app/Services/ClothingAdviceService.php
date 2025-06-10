<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class ClothingAdviceService
{
  public function suggestClothing(array $weatherData): array
  {
    // 取得した情報を元にプロンプトを作成
    $prompt =
      "今日の天気予報は以下の通りです：\n"
      . "最高気温: {$weatherData['max']}℃\n"
      . "最低気温: {$weatherData['min']}℃\n"
      . "降水確率: 最大 {$weatherData['pop']}%、平均 {$weatherData['avgPop']}%\n"
      . "平均湿度: {$weatherData['humidityAvg']}%"
      . "平均風速: {$weatherData['windAvg']} m/s"
      . "この天候に適した服装のアドバイスを簡潔に日本語でお願いします。";

    $client = Gemini::generativeModel("models/gemini-1.5-flash-001");
    try {
      $response = $client->generateContent($prompt);
      $text = $response->text();
    } catch (\Exception $e) {
      Log::error("Gemini APIエラー: " . $e->getMessage());
      $text = 'アドバイスを取得できませんでした。';
    }

    return [
      'advice' => $text,
      'category' => 'AIによる提案',
    ];
  }
}
