<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->string('vkn_tckn')->unique();
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('tax_office_name')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('TR');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->enum('company_type', ['Company', 'Individual'])->default('Company');
            $table->string('sector')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['Active', 'Passive'])->default('Active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stakeholders');
    }
};
