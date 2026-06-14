<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alice_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('action', 20); // created / updated / deleted
            $table->string('table_name', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('source', 50)->default('alice');
            $table->string('ip', 45)->nullable();
            $table->string('idempotency_key', 255)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->boolean('dry_run')->default(false);
            $table->timestamp('created_at')->useCurrent();
            // no updated_at — audit log is immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alice_audit_log');
    }
};
