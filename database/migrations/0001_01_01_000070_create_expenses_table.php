<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('stakeholder_id')->nullable()->constrained('stakeholders')->nullOnDelete();
            $table->foreignId('expense_type_id')->nullable()->constrained('expense_types')->nullOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->boolean('company_expense')->default(false);
            $table->boolean('paid_by_others')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['date', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
