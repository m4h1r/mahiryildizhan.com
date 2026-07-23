<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('calories_per_100g');
            $table->decimal('carbs_per_100g', 6, 2)->default(0);
            $table->decimal('sugar_per_100g', 6, 2)->default(0);
            $table->decimal('fat_per_100g', 6, 2)->default(0);
            $table->enum('unit_type', ['gram', 'piece'])->default('gram');
            $table->decimal('grams_per_unit', 6, 2)->nullable();
            $table->json('vitamins')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
