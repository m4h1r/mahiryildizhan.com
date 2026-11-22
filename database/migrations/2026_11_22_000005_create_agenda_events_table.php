<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S5): agenda events synced from Alice (Pi). source_ref (e.g. gcal:<id>)
 * is unique so an imported calendar event lands once.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agenda_events')) {
            return;
        }

        Schema::create('agenda_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('version')->default(1);
            $table->string('title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_ref')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_events');
    }
};
