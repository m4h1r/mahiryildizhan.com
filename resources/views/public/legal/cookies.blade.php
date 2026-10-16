@extends('public.layout', [
    'title' => __('Cookie Policy').' | '.config('app.name'),
    'description' => __('Which cookies this website uses and how to control them.'),
    'robots' => 'index,follow',
])

@section('content')
    <article class="prose prose-gray mx-auto max-w-3xl dark:prose-invert">
        <h1>{{ __('Cookie Policy') }}</h1>
        <x-legal-meta />

        @if (app()->getLocale() === 'en')
            <p>We use a small number of cookies. Strictly necessary cookies (session, CSRF, your
               theme and language choice) are always active. Analytics and advertising cookies load
               only after you accept them in the consent banner.</p>
            <h2>Managing cookies</h2>
            <p>You can change your choice at any time by clearing site data in your browser or using
               the consent controls. Declining analytics does not affect reading the site.</p>
        @else
            <p>Az sayıda çerez kullanıyoruz. Kesinlikle gerekli çerezler (oturum, CSRF, tema ve dil
               tercihiniz) her zaman etkindir. Analitik ve reklam çerezleri yalnızca onay bandında
               kabul ettikten sonra yüklenir.</p>
            <h2>Çerezleri yönetme</h2>
            <p>Seçiminizi istediğiniz zaman tarayıcınızdan site verilerini temizleyerek veya onay
               kontrolleriyle değiştirebilirsiniz. Analitiği reddetmek siteyi okumanızı etkilemez.</p>
        @endif

        <x-legal-notice />
    </article>
@endsection
