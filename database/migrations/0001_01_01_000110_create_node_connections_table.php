<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_from_id')->constrained('nodes')->cascadeOnDelete();
            $table->foreignId('node_to_id')->constrained('nodes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['node_from_id', 'node_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_connections');
    }
};
