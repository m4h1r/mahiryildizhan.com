<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S5): reading log entries synced from Alice (Pi).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reading_logs')) {
            return;
        }

        Schema::create('reading_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('version')->default(1);
            $table->string('book_title');
            $table->string('author')->nullable();
            $table->unsignedInteger('pages');
            $table->date('read_on');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('read_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_logs');
    }
};
