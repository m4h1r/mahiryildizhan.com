<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('government_acceptance_percentage')
                ->default(100)
                ->after('name');
        });

        DB::table('expense_types')->update([
            'government_acceptance_percentage' => 100,
        ]);

        DB::table('expense_types')
            ->whereRaw('LOWER(name) = ?', ['fuel'])
            ->update(['government_acceptance_percentage' => 70]);

        DB::table('expense_types')
            ->whereRaw('LOWER(name) = ?', ['meal'])
            ->update(['government_acceptance_percentage' => 100]);
    }

    public function down(): void
    {
        Schema::table('expense_types', function (Blueprint $table) {
            $table->dropColumn('government_acceptance_percentage');
        });
    }
};
