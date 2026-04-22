<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test
                            {--local  : Log-only test — no real SMTP, checks template + queue wiring}
                            {--live   : Real SMTP test — sends actual email, use on VPS to confirm delivery}
                            {--to=    : Override recipient address}';

    protected $description = 'Test the mail service. Use --local in dev, --live on the VPS.';

    public function handle(): int
    {
        $isLocal = $this->option('local');
        $isLive  = $this->option('live');

        if (! $isLocal && ! $isLive) {
            $this->error('Bir mod seçin: --local veya --live');
            $this->line('  php artisan mail:test --local   → log dosyasına yazar, SMTP gerekmez');
            $this->line('  php artisan mail:test --live    → gerçek SMTP ile e-posta gönderir');

            return self::FAILURE;
        }

        $to = $this->option('to')
            ?? config('mail.admin_address', config('mail.from.address'));

        // ── LOCAL MODE ────────────────────────────────────────────────────────
        if ($isLocal) {
            $this->line('');
            $this->info('═══ YEREL TEST (log mailer) ════════════════════════');

            Config::set('mail.default', 'log');

            $this->line("Alıcı : {$to}");
            $this->line('Mailer: log → storage/logs/laravel.log');

            try {
                Mail::raw(
                    $this->body('local'),
                    fn (Message $m) => $m->to($to)->subject('[LOCAL] Mail Servisi Test — ' . config('app.name'))
                );

                $this->info('✅ Log mailer başarılı.');
                $this->line('   → tail -f storage/logs/laravel.log | grep "Message-ID"');
                $this->line('   → E-posta içeriği log dosyasına yazıldı.');

                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error('❌ Log mailer hatası: ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        // ── LIVE MODE ─────────────────────────────────────────────────────────
        $this->line('');
        $this->info('═══ CANLI TEST (gerçek SMTP) ════════════════════════');
        $this->line("Alıcı : {$to}");
        $this->line('Mailer: ' . config('mail.default'));
        $this->line('Host  : ' . config('mail.mailers.smtp.host', '—'));
        $this->line('Port  : ' . config('mail.mailers.smtp.port', '—'));
        $this->line('From  : ' . config('mail.from.address'));
        $this->line('');

        if (! $this->confirm('Yukarıdaki SMTP ayarlarıyla devam edilsin mi?', true)) {
            $this->line('İptal edildi.');

            return self::SUCCESS;
        }

        try {
            Mail::raw(
                $this->body('live'),
                fn (Message $m) => $m->to($to)->subject('[LIVE] Mail Servisi Test — ' . config('app.name'))
            );

            $this->info('✅ Test e-postası gönderildi!');
            $this->line("   → Gelen kutusunu kontrol edin: {$to}");
            $this->line('   → Spam klasörüne düştüyse SPF/DKIM kayıtlarını kontrol edin.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Gönderim başarısız: ' . $e->getMessage());
            $this->line('');
            $this->line('Kontrol listesi:');
            $this->line('  [ ] .env: MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD doğru mu?');
            $this->line('  [ ] Gmail: App Password oluşturdunuz mu? (16 karakter)');
            $this->line('  [ ] Güvenlik duvarı: port 587 açık mı? (telnet smtp.gmail.com 587)');
            $this->line('  [ ] MAIL_FROM_ADDRESS, Gmail ile gönderiyorsanız Gmail adresiyle eşleşmeli.');

            return self::FAILURE;
        }
    }

    private function body(string $mode): string
    {
        return implode("\n", [
            'Bu bir ' . ($mode === 'local' ? 'YEREL (log)' : 'CANLI (SMTP)') . ' test e-postasıdır.',
            '',
            'Proje  : ' . config('app.name'),
            'Ortam  : ' . app()->environment(),
            'URL    : ' . config('app.url'),
            'Mailer : ' . config('mail.default'),
            'Zaman  : ' . now()->format('d.m.Y H:i:s T'),
            '',
            'Mail servisi düzgün çalışıyorsa bu e-postayı aldınız demektir.',
        ]);
    }
}
