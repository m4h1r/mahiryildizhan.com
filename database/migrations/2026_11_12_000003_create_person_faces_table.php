<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Biometric data (KVKK special category). Images live on a private disk,
        // embeddings are encrypted at rest through the model's `encrypted:array` cast.
        Schema::create('person_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->string('image_path')->nullable();
            $table->longText('embedding')->nullable();
            $table->string('embedding_model', 100)->nullable();
            $table->unsignedSmallInteger('embedding_dim')->nullable();
            $table->json('bbox')->nullable();
            $table->json('landmarks')->nullable();
            $table->decimal('quality', 5, 4)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('source', 20)->default('manual');
            $table->timestamp('captured_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_faces');
    }
};
