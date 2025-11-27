<x-mail::message>
  # パスワードの再設定が完了しました 🔐

  {{ $user->name }} 様

  パスワードの再設定が正常に完了しました。

  覚えのない変更が行われた場合は、速やかにアカウントの保護を行ってください。

  <x-mail::button :url="url('/')">
    アプリを開く
  </x-mail::button>

  ---

  ご不明点がございましたらお気軽にお問い合わせください。
  <br>
  引き続き {{ config('app.name') }} をよろしくお願いいたします。

  <br>

  @include('emails.user.partials.footer')
</x-mail::message>