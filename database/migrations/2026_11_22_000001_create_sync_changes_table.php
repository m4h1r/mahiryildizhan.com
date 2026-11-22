<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S2): the append-only change log Alice (Pi) pulls from. Each row is a
 * cursor point; `id` is the cursor. Written by the Syncable observer after every
 * create/update (upsert) and soft/force delete (delete).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sync_changes')) {
            return;
        }

        Schema::create('sync_changes', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('entity', 32);
            $table->string('record_id', 64);
            $table->enum('op', ['upsert', 'delete']);
            $table->unsignedInteger('version');
            $table->timestamp('created_at')->useCurrent();

            // `id` is already the primary-key cursor; this speeds per-record lookups.
            $table->index(['entity', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_changes');
    }
};
