<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per evaluation (versioned snapshots). The newest by assessed_at
        // is the person's current inventory; older rows are the history.
        Schema::create('person_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->date('assessed_at');
            $table->string('source', 20)->default('manual');
            $table->decimal('overall_confidence', 3, 2)->nullable();
            $table->string('relationship_label')->nullable();

            $table->json('big_five')->nullable();
            $table->json('relationship_bases')->nullable();
            $table->json('metrics')->nullable();
            $table->string('attachment_style', 20)->nullable();
            $table->text('attachment_note')->nullable();
            $table->json('values')->nullable();
            $table->json('dark_triad')->nullable();
            $table->text('dark_triad_note')->nullable();
            $table->json('approach')->nullable();

            $table->longText('summary')->nullable();
            $table->longText('notes')->nullable();
            $table->text('history_note')->nullable();
            $table->unsignedInteger('source_message_count')->nullable();
            $table->json('raw')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'assessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_assessments');
    }
};
