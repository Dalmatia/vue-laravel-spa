<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\Color;
use App\Enums\Gender;
use App\Models\User;

class PromptBuilder
{
  public function buildJson(
    array $weatherData,
    ?User $user = null,
    ?string $tpo = null
  ): string {
    $genderText = $this->mapGenderToText(Gender::coerce($user?->gender));
    $ageText = $this->mapAgeToText($user?->age);
    $tpoText = $this->mapTpoToText($tpo);

    return <<<PROMPT
      あなたはファッションの専門家です。

      【重要】
      - 出力は **JSONのみ**
      - 説明文・前置き・コメント・コードブロックは禁止
      - JSONは必ず **1オブジェクト**
      - キー名は下記で指定したもの以外は絶対に使わない
      - null は使わず、必ず配列で返す
      - 不要なカテゴリは **空配列 []** にする

      【使用可能なカテゴリキー（厳守）】
      - "tops"
      - "bottoms"
      - "shoes"
      - "outer"

      【JSONフォーマット（完全一致）】
      {
        "summary": "string",
        "items": {
          "tops": ["string"],
          "bottoms": ["string"],
          "shoes": ["string"],
          "outer": ["string"]
        },
        "notes": ["string"]
      }

      【ユーザー情報】
      - 性別: {$genderText}
      - 年齢: {$ageText}
      - シーン: {$tpoText}

      【天気】
      - 最高気温: {$weatherData['max']}℃
      - 最低気温: {$weatherData['min']}℃
      - 降水確率: {$weatherData['pop']}%
      - 湿度: {$weatherData['humidityAvg']}%
      - 風速: {$weatherData['windAvg']} m/s

      【制約】
      - summary は 40文字以内
      - items の配列要素は 1〜3 件
      - 抽象語ではなく「白シャツ」「黒スラックス」など具体名にする
      - JSON以外は絶対に出力しない
    PROMPT;
  }



  private function mapGenderToText(?Gender $gender): string
  {
    if (!$gender instanceof Gender) {
      return '';
    }

    return match ($gender->value) {
      Gender::Male   => '男性',
      Gender::Female => '女性',
      Gender::Kids  => 'キッズ',
      Gender::NotSet => '',
      default => '',
    };
  }

  private function mapAgeToText(?int $age): string
  {
    if (!$age) return '年齢未設定';
    return match (true) {
      $age < 20  => '10代',
      $age < 30  => '20代',
      $age < 40  => '30代',
      $age < 50  => '40代',
      default    => '50代以上',
    };
  }

  private function mapTpoToText(?string $tpo): string
  {
    if (!$tpo) return '特に指定なし';
    return match ($tpo) {
      'casual'   => 'カジュアル',
      'date'     => 'デート',
      'office' => 'オフィスカジュアル',
      'outdoor'  => 'アウトドア',
      default    => 'カジュアル',
    };
  }
}
