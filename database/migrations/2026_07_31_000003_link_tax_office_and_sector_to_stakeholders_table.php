<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->foreignId('tax_office_id')->nullable()->after('vkn_tckn')->constrained('tax_offices')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->after('tax_office_id')->constrained('sectors')->nullOnDelete();
        });

        $this->backfill('tax_office_name', 'tax_offices', 'tax_office_id');
        $this->backfill('sector', 'sectors', 'sector_id');

        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropColumn(['tax_office_name', 'sector']);
        });
    }

    public function down(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->string('tax_office_name')->nullable()->after('vkn_tckn');
            $table->string('sector')->nullable()->after('tax_office_name');
        });

        $this->restore('tax_offices', 'tax_office_id', 'tax_office_name');
        $this->restore('sectors', 'sector_id', 'sector');

        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_office_id');
            $table->dropConstrainedForeignId('sector_id');
        });
    }

    private function backfill(string $sourceColumn, string $dictionaryTable, string $foreignKey): void
    {
        $values = DB::table('stakeholders')
            ->whereNotNull($sourceColumn)
            ->where($sourceColumn, '!=', '')
            ->distinct()
            ->pluck($sourceColumn);

        foreach ($values as $value) {
            $name = trim((string) $value);

            if ($name === '') {
                continue;
            }

            $id = DB::table($dictionaryTable)->where('name', $name)->value('id')
                ?? DB::table($dictionaryTable)->insertGetId([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('stakeholders')->where($sourceColumn, $value)->update([$foreignKey => $id]);
        }
    }

    private function restore(string $dictionaryTable, string $foreignKey, string $targetColumn): void
    {
        DB::table('stakeholders')
            ->whereNotNull($foreignKey)
            ->orderBy('id')
            ->each(function ($stakeholder) use ($dictionaryTable, $foreignKey, $targetColumn): void {
                $name = DB::table($dictionaryTable)->where('id', $stakeholder->{$foreignKey})->value('name');

                if ($name !== null) {
                    DB::table('stakeholders')->where('id', $stakeholder->id)->update([$targetColumn => $name]);
                }
            });
    }
};
