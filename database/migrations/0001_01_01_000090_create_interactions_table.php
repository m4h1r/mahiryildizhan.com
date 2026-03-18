<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('interaction_type_id')->nullable()->constrained('interaction_types')->nullOnDelete();
            $table->date('date');
            $table->string('effect')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['person_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
