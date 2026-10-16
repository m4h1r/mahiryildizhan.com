@extends('public.layout', [
    'title' => __('Terms of Use').' | '.config('app.name'),
    'description' => __('The terms that govern use of this website.'),
    'robots' => 'index,follow',
])

@section('content')
    <article class="prose prose-gray mx-auto max-w-3xl dark:prose-invert">
        <h1>{{ __('Terms of Use') }}</h1>
        <x-legal-meta />

        @if (app()->getLocale() === 'en')
            <p>By using {{ config('app.name') }} you agree to these terms. Content published here is
               the author's own and is provided for information only. You may read and share links
               freely; reproducing full articles requires permission.</p>
            <h2>User contributions</h2>
            <p>Comments must be lawful and respectful. We may remove any comment and reserve the
               right to moderate submissions.</p>
            <h2>Liability</h2>
            <p>The site is provided "as is" without warranties. The owner is not liable for decisions
               made based on its content.</p>
        @else
            <p>{{ config('app.name') }} sitesini kullanarak bu koşulları kabul edersiniz. Burada
               yayımlanan içerik yazara aittir ve yalnızca bilgilendirme amaçlıdır. Bağlantıları
               serbestçe okuyabilir ve paylaşabilirsiniz; yazıların tamamının çoğaltılması izne tabidir.</p>
            <h2>Kullanıcı katkıları</h2>
            <p>Yorumlar hukuka uygun ve saygılı olmalıdır. Her yorumu kaldırma ve gönderileri
               denetleme hakkımız saklıdır.</p>
            <h2>Sorumluluk</h2>
            <p>Site "olduğu gibi", garanti verilmeksizin sunulur. İçeriğe dayanarak alınan kararlardan
               site sahibi sorumlu değildir.</p>
        @endif

        <x-legal-notice />
    </article>
@endsection
