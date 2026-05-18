<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AboutContentSeeder extends Seeder
{
    public function run(): void
    {
        $contentEn = '<p><strong>Mahir Yıldızhan</strong> — a software developer, writer, and independent thinker based in Turkey. I build web applications, explore ideas through long-form writing, and document the process along the way.</p><p>This site is my personal publishing home. It\'s where I share thoughts on technology, development, life, and whatever else occupies my mind. The writing is intentional, the interface is minimal, and the goal is to create a calm reading experience.</p><h2>What I work on</h2><ul><li>Full-stack web development with Laravel and modern JavaScript</li><li>Building personal tools for productivity and knowledge management</li><li>Writing about software, ideas, and the intersections between them</li><li>Photography, design, and visual storytelling on the side</li></ul><h2>This site</h2><p>Built with Laravel, Tailwind CSS, and a custom admin panel. It runs a blog, a personal timeline, a CRM layer, and financial tracking — all under one roof. Everything is designed to stay lean and readable.</p><h2>Get in touch</h2><p>You can find me on <a href="https://github.com/m4h1r" target="_blank" rel="noopener noreferrer">GitHub</a> and <a href="https://www.linkedin.com/in/mahiryildizhan/" target="_blank" rel="noopener noreferrer">LinkedIn</a>. For everything else, feel free to reach out directly.</p>';

        $contentTr = '<p><strong>Mahir Yıldızhan</strong> — Türkiye\'de yaşayan bir yazılım geliştirici, yazar ve bağımsız düşünür. Web uygulamaları geliştiriyor, uzun biçimli yazılarla fikirlerimi keşfediyor ve bu süreci belgeliyorum.</p><p>Bu site, kişisel yayın alanım. Teknoloji, yazılım geliştirme, hayat ve aklımı meşgul eden her şey hakkında düşüncelerimi paylaştığım bir yer. Yazılar özenle seçilmiş, arayüz minimalist ve hedef, sakin bir okuma deneyimi sunmak.</p><h2>Neler üzerinde çalışıyorum</h2><ul><li>Laravel ve modern JavaScript ile full-stack web geliştirme</li><li>Verimlilik ve bilgi yönetimi için kişisel araçlar inşa etmek</li><li>Yazılım, fikirler ve aralarındaki kesişimler üzerine yazmak</li><li>Yan uğraş olarak fotoğrafçılık, tasarım ve görsel hikaye anlatımı</li></ul><h2>Bu site hakkında</h2><p>Laravel, Tailwind CSS ve özel bir yönetim paneliyle inşa edildi. Blog, kişisel zaman çizelgesi, CRM katmanı ve finansal takip — hepsi tek çatı altında. Her şey sade ve okunabilir kalacak şekilde tasarlandı.</p><h2>İletişim</h2><p>Beni <a href="https://github.com/m4h1r" target="_blank" rel="noopener noreferrer">GitHub</a> ve <a href="https://www.linkedin.com/in/mahiryildizhan/" target="_blank" rel="noopener noreferrer">LinkedIn</a>\'de bulabilirsiniz. Diğer her şey için doğrudan iletişime geçmekten çekinmeyin.</p>';

        Setting::query()->updateOrCreate(
            ['key' => 'about_content_en'],
            [
                'value' => $contentEn,
                'group' => 'about',
                'is_secret' => false,
                'description' => 'About Page (English)',
            ]
        );

        Setting::query()->updateOrCreate(
            ['key' => 'about_content_tr'],
            [
                'value' => $contentTr,
                'group' => 'about',
                'is_secret' => false,
                'description' => 'About Page (Turkish)',
            ]
        );
    }
}
