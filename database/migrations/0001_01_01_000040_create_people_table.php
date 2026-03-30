<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('second_surname')->nullable();
            $table->date('birthday')->nullable();
            $table->date('deathday')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('death_place')->nullable();
            $table->foreignId('father_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('mother_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('gender_id')->nullable()->constrained('genders')->nullOnDelete();
            $table->foreignId('eye_color_id')->nullable()->constrained('eye_colors')->nullOnDelete();
            $table->foreignId('blood_type_id')->nullable()->constrained('blood_types')->nullOnDelete();
            $table->foreignId('hair_color_id')->nullable()->constrained('hair_colors')->nullOnDelete();
            $table->string('picture')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['surname', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
