@extends('public.layout', [
    'title' => __('Privacy Policy').' | '.config('app.name'),
    'description' => __('How this website collects, uses and protects personal data.'),
    'robots' => 'index,follow',
])

@section('content')
    <article class="prose prose-gray mx-auto max-w-3xl dark:prose-invert">
        <h1>{{ __('Privacy Policy') }}</h1>
        <x-legal-meta />

        @if (app()->getLocale() === 'en')
            <p>This website ({{ config('app.name') }}) respects your privacy. We collect only the
               data needed to operate the site: your e-mail address when you subscribe to the
               newsletter, and your name/e-mail when you post a comment. We never sell personal data.</p>
            <h2>What we collect</h2>
            <ul>
                <li>Newsletter: e-mail address (with your consent).</li>
                <li>Comments: name, e-mail, and a hashed IP address for spam prevention.</li>
                <li>Analytics: anonymous usage data, only after you accept cookies.</li>
            </ul>
            <h2>Your rights</h2>
            <p>You may request access, correction or deletion of your data at any time by contacting
               the site owner. Newsletter e-mails can be unsubscribed from any message.</p>
        @else
            <p>Bu web sitesi ({{ config('app.name') }}) gizliliğinize saygı duyar. Yalnızca sitenin
               işlemesi için gereken verileri toplarız: bültene abone olurken e-posta adresiniz,
               yorum yazarken ad/e-posta bilgileriniz. Kişisel verileriniz asla satılmaz.</p>
            <h2>Toplanan veriler</h2>
            <ul>
                <li>Bülten: e-posta adresi (açık rızanızla).</li>
                <li>Yorumlar: ad, e-posta ve spam önleme için hash'lenmiş IP adresi.</li>
                <li>Analitik: yalnızca çerezleri kabul ettikten sonra anonim kullanım verisi.</li>
            </ul>
            <h2>Haklarınız</h2>
            <p>Verilerinize erişim, düzeltme veya silme talebinizi istediğiniz zaman site sahibine
               ileterek iletebilirsiniz. Bülten e-postalarından her mesajdan çıkabilirsiniz.</p>
        @endif

        <x-legal-notice />
    </article>
@endsection
