<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AliceRotateToken extends Command
{
    protected $signature = 'alice:rotate-token {--user-id= : Admin kullanıcı ID (belirtilmezse ilk admin)}';

    protected $description = 'Alice Bridge token\'ını yenile ve storage/app/alice/.env.alice dosyasına yaz';

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $user = $userId
            ? User::where('id', $userId)->where('is_admin', true)->first()
            : User::where('is_admin', true)->first();

        if (! $user) {
            $this->error('Admin kullanıcı bulunamadı.');

            return self::FAILURE;
        }

        $tokenName = config('alice.token_name', 'alice-bridge');

        // Revoke existing alice tokens
        $revoked = $user->tokens()->where('name', $tokenName)->count();
        $user->tokens()->where('name', $tokenName)->delete();

        if ($revoked > 0) {
            $this->info("Eski {$revoked} token iptal edildi.");
        }

        // Create new token
        $newToken = $user->createToken($tokenName);
        $plainText = $newToken->plainTextToken;

        // Write to .env.alice
        $envDir = storage_path('app/alice');
        if (! is_dir($envDir)) {
            mkdir($envDir, 0755, true);
        }

        $envContent = "# Alice Bridge API Token\n";
        $envContent .= '# Üretildi: '.now()->toIso8601String()."\n";
        $envContent .= 'ALICE_PANEL_URL='.config('app.url')."\n";
        $envContent .= "ALICE_PANEL_TOKEN={$plainText}\n";
        $envContent .= "ALICE_BASE_PATH=/api/v1/alice\n";

        file_put_contents($envDir.'/.env.alice', $envContent);

        $this->info('Yeni token üretildi.');
        $this->info('Token: '.$plainText);
        $this->info('Kaydedildi: storage/app/alice/.env.alice');
        $this->newLine();
        $this->comment('Alice kurulum komutu:');
        $this->line("export ALICE_PANEL_TOKEN='{$plainText}'");
        $this->line("export ALICE_PANEL_URL='".config('app.url')."'");

        return self::SUCCESS;
    }
}
