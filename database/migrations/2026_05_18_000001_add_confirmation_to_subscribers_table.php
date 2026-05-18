<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('confirmation_token', 64)->nullable()->after('mailchimp_id');
            $table->timestamp('confirmed_at')->nullable()->after('confirmation_token');
        });

        // Extend status enum to include 'pending'
        DB::statement("ALTER TABLE subscribers MODIFY COLUMN status ENUM('pending', 'active', 'unsubscribed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Restore original enum before dropping columns
        DB::statement("ALTER TABLE subscribers MODIFY COLUMN status ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active'");

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['confirmation_token', 'confirmed_at']);
        });
    }
};
