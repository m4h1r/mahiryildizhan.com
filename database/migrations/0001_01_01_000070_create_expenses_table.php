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
            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $tab            $t->boolean('company_expense')->default(false);
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
