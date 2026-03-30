<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('income_source_id')->nullable()->constrained('income_sources')->nullOnDelete();
            $table->foreignId('income_type_id')->nullable()->constrained('income_types')->nullOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['date', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
