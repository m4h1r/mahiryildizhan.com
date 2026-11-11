<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_channels', function (Blueprint $table) {
            $table->id();
            // D9: no cascade on a SoftDeletes parent — Person::forceDeleting cleans up.
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->string('type', 30);
            $table->string('value', 500);
            $table->string('label')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_channels');
    }
};
