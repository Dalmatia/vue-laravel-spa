<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\Gender;
use App\Models\User;

class PromptBuilder
{
  public function build(array $weatherData, ?User $user = null, ?string $tpo = null): string
  {
    $genderType = Gender::coerce($user?->gender);
    $genderText = $this->mapGenderToText($genderType);
    $ageText = $this->mapAgeToText($user?->age);
    $tpoText = $this->mapTpoToText($tpo);

    // 取得した天気情報を元にプロンプトを作成
    return <<<PROMPT
      あなたはファッションの専門家です。以下の天気情報とユーザー属性をもとに、パーソナライズされた服装アドバイスを日本語で提案してください。

      【ユーザー情報】
        - 性別: {$genderText}
        - 年齢: {$ageText}

      【シーン（TPO）】
        - {$tpoText}

      【天気データ】
        - 最高気温: {$weatherData['max']}℃
        - 最低気温: {$weatherData['min']}℃
        - 降水確率（最大）: {$weatherData['pop']}%
        - 降水確率（平均）: {$weatherData['avgPop']}%
        - 平均湿度: {$weatherData['humidityAvg']}%
        - 平均風速: {$weatherData['windAvg']} m/s

      【制約】
        - 出力は150文字以内
        - 性別・年齢・シーンに合ったファッション提案にすること
        - 柄×柄などのミスマッチを避ける
        - 必ず以下の4カテゴリについて触れてください:
          1. トップス
          2. ボトムス
          3. シューズ
          4. アウター（必要なら）
        
        【出力例】
        例: 「デートの日は20代男性なら白シャツに黒のスラックスで清潔感を。足元は革靴で上品にまとめ、夜は軽いジャケットを羽織ると◎。」

        以上を踏まえて、今日の服装アドバイスをお願いします。
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
      default    => '特に指定なし',
    };
  }
}
