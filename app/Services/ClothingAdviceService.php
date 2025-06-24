<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class ClothingAdviceService
{
  public function suggestClothing(array $weatherData): array
  {
    // 取得した情報を元にプロンプトを作成
    $prompt = <<<PROMPT
      あなたはファッションの専門家です。以下の天気情報を元に、ユーザーが快適に過ごせる服装のアドバイスを日本語で提案してください。

      【天気データ】
        - 最高気温: {$weatherData['max']}℃
        - 最低気温: {$weatherData['min']}℃
        - 降水確率（最大）: {$weatherData['pop']}%
        - 降水確率（平均）: {$weatherData['avgPop']}%
        - 平均湿度: {$weatherData['humidityAvg']}%
        - 平均風速: {$weatherData['windAvg']} m/s

      【制約】
        - 出力は100文字以内
        - 読者は20代の一般的な男女
        - 季節や気温、降水確率に基づき具体的なアイテム（例：コート、半袖、傘）を含めてください
        - 避けた方がよい服装もあれば触れてください
        - TPOは日常の外出を想定しています

      【出力例】
        例: 「昼は暑くなりそうなので薄手のシャツを。夜は冷えるため羽織りがあると安心です。折りたたみ傘も忘れずに。」

      以上を踏まえて、今日の服装アドバイスをお願いします。
    PROMPT;


    $client = Gemini::generativeModel("gemini-2.0-flash-001");
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
