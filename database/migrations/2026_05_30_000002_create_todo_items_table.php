<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->decimal('cost_try', 10, 2)->nullable();
            $table->decimal('time_cost_hours', 6, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_bucketlist')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_completed', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_items');
    }
};
