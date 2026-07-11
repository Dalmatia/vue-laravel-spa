<?php

namespace App\Domain\ClothingAdvice;

use App\Domain\Weather\WeatherDto;
use App\Enums\Gender;
use App\Models\User;

class PromptBuilder
{
  public function buildJson(
    WeatherDto $weatherData,
    ?User $user = null,
    ?string $tpo = null
  ): string {
    $genderText = $this->mapGenderToText(Gender::coerce($user?->gender));
    $ageText = $this->mapAgeToText($user?->age);
    $tpoText = $this->mapTpoToText($tpo);

    return <<<PROMPT
      あなたはファッションの専門家です。

      以下のJSONを返してください。

      summary:
      今日の服装を一文で説明

      items:
      outer
      tops
      bottoms
      shoes

      notes:
      3件程度
    PROMPT;
  }

  public function build(
    AdviceGenerationMode $mode,
    WeatherDto $weatherData,
    ?User $user,
    ?string $tpo
  ): string {
    return match ($mode) {
      AdviceGenerationMode::OUTFIT_BASED =>
      $this->buildJson($weatherData, $user, $tpo),

      AdviceGenerationMode::GENERAL_ADVICE =>
      $this->buildGeneralAdviceJson($weatherData, $user, $tpo),
    };
  }

  private function buildGeneralAdviceJson(
    WeatherDto $weatherData,
    ?User $user,
    ?string $tpo
  ): string {
    $tpoText = $this->mapTpoToText($tpo);

    return <<<PROMPT
      あなたは服装アドバイザーです。

      【重要】
      - 具体的な服装・コーディネートは想定しない
      - 「この服装」「このコーデ」「〜を着る」などの表現は禁止
      - 特定の商品名・ブランド名は出さない
      - 服装の「構成」や「方向性」は説明してよい
      - 天気とシーンに基づいた一般的な考え方・注意点のみを述べる

      【制約】
      - 以下のカテゴリそれぞれについて「どういう考え方が良いか」を述べる
      - アウター（不要な場合はその理由）
      - トップス
      - ボトムス
      - シューズ

      【summary のルール】
      - 状況説明 + 方針を簡潔に述べる
      - 例：
      - 「寒さを考慮した服装のポイント」
      - 「{$tpoText}シーンに適した服装の考え方」

      【notes の例】
      - トップスは体温を逃しにくい厚手の素材を意識し、
        首元まで覆えるデザインだと寒さ対策になります。
      - ボトムスは風を通しにくい素材感を選ぶことで、
        下半身の冷えを防ぎやすくなります。
      - シューズは足元から冷えやすいため、
        くるぶしまで覆えるタイプが安心です。
      - 気温が低いため、アウターは必須で、
        防風性を重視すると快適に過ごせます。
      
      出力は JSON のみ。

      {
        "summary": "string",
        "notes": ["string"]
      }
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
