@extends('public.layout', [
    'title' => __('KVKK Disclosure').' | '.config('app.name'),
    'description' => __('Disclosure under Turkish Personal Data Protection Law (KVKK).'),
    'robots' => 'index,follow',
])

@section('content')
    <article class="prose prose-gray mx-auto max-w-3xl dark:prose-invert">
        <h1>{{ __('KVKK Disclosure') }}</h1>
        <x-legal-meta />

        @if (app()->getLocale() === 'en')
            <p>Under the Turkish Personal Data Protection Law No. 6698 (KVKK), the data controller
               is the owner of {{ config('app.name') }}. Personal data (e-mail for the newsletter;
               name, e-mail and hashed IP for comments) is processed solely to operate this site.</p>
            <h2>Retention</h2>
            <p>Unsubscribed newsletter addresses are deleted after
               {{ config('retention.policies.subscribers_unsubscribed.days') }} days; unapproved comments
               after {{ config('retention.policies.comments_unapproved.days') }} days. These periods match
               our enforced retention policy.</p>
            <h2>Legal basis &amp; rights</h2>
            <p>Processing relies on your explicit consent. You hold the rights set out in Article 11
               of the KVKK, including access, correction and erasure. Requests reach the data
               controller via the contact channel on this site.</p>
        @else
            <p>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında veri sorumlusu
               {{ config('app.name') }} sahibidir. Kişisel verileriniz (bülten için e-posta; yorumlar
               için ad, e-posta ve hash'lenmiş IP) yalnızca bu sitenin işletilmesi amacıyla işlenir.</p>
            <h2>Saklama süreleri</h2>
            <p>Aboneliği iptal edilen e-postalar {{ config('retention.policies.subscribers_unsubscribed.days') }} gün,
               onaylanmayan yorumlar {{ config('retention.policies.comments_unapproved.days') }} gün sonra silinir.
               Bu süreler uyguladığımız veri saklama politikasıyla uyumludur.</p>
            <h2>Hukuki dayanak ve haklarınız</h2>
            <p>İşleme açık rızanıza dayanır. KVKK'nın 11. maddesinde sayılan erişim, düzeltme ve silme
               dahil tüm haklara sahipsiniz. Talepleriniz bu sitedeki iletişim kanalı üzerinden veri
               sorumlusuna ulaşır.</p>
        @endif

        <x-legal-notice />
    </article>
@endsection
