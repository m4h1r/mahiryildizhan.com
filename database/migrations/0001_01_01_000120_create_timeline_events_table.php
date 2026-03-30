<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['milestone', 'process'])->default('milestone');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#3B82F6');
            $table->boolean('is_public')->default(true);
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['is_public', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
