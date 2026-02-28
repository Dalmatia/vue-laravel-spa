<x-mail::message>
  # 退会手続きが完了しました 🙇‍♂️

  {{ $user->name }} 様

  ご利用いただき誠にありがとうございました。
  退会手続きが正常に完了しました。

  <x-mail::button :url="url('/')">
    ホームページを見る
  </x-mail::button>

  ---

  またお会いできる日を心よりお待ちしております。
  <br>
  {{ config('app.name') }} 一同

  <br>

  @include('emails.user.partials.footer')
</x-mail::message>