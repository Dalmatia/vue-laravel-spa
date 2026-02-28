<x-mail::message>
  # ご登録ありがとうございます 🎉

  {{ $user->name }} 様

  この度は **{{ config('app.name') }}** にご登録いただき、誠にありがとうございます。

  あなたのファッションライフをさらに楽しくするための
  コーディネート投稿・お気に入り保存・フォロー機能などをぜひご活用ください！

  <x-mail::button :url="url('/')">
    アプリを開く
  </x-mail::button>

  ---

  今後も便利な機能追加を予定しております。
  <br>
  引き続き {{ config('app.name') }} をよろしくお願いいたします。

  <br>

  @include('emails.user.partials.footer')
</x-mail::message>