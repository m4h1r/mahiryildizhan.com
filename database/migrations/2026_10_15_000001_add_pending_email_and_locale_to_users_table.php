<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // AC3 — e-mail change is an account transfer: the new address is
            // parked here until its verification link is clicked; users.email
            // is only rewritten then.
            $table->string('pending_email')->nullable()->after('email');
            $table->string('pending_email_token')->nullable()->after('pending_email');
            $table->timestamp('pending_email_expires_at')->nullable()->after('pending_email_token');

            // AC2/R2 — preferred locale for the UI and for queued mail.
            $table->string('locale', 5)->default('tr')->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pending_email',
                'pending_email_token',
                'pending_email_expires_at',
                'locale',
            ]);
        });
    }
};
