<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S5): wealth / net-worth entries synced from Alice (Pi). Distinct from
 * the cashflow tables (incomes/expenses) — these are point-in-time balances.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wealth_entries')) {
            return;
        }

        Schema::create('wealth_entries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('version')->default(1);
            $table->string('kind');
            $table->string('label');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3);
            $table->date('as_of');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('as_of');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wealth_entries');
    }
};
