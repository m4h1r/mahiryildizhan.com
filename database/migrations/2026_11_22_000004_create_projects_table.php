<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S5): projects synced from Alice (Pi). Keyed by id + slug.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            return;
        }

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('version')->default(1);
            $table->string('slug', 80)->unique();
            $table->string('name');
            $table->string('area')->nullable();
            $table->enum('status', ['fikir', 'gelistirme', 'aktif', 'pasif', 'arsiv'])->default('fikir');
            $table->unsignedTinyInteger('progress')->nullable();
            $table->text('note')->nullable();
            $table->date('due')->nullable();
            $table->boolean('due_postponed')->default(false);
            $table->date('idea_date')->nullable();
            $table->string('local_path')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('scope_ref')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
