<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S4): a stable external reference for an assessment so Alice (Pi) can
 * create the same card once. Unique per person; a repeat → 409 with the existing id.
 * Pi uses `alice-card:<slug>:<assessed_at>` and `relations-db:<person_id>`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('person_assessments', 'external_ref')) {
            return;
        }

        Schema::table('person_assessments', function (Blueprint $table): void {
            $table->string('external_ref', 191)->nullable()->after('source');
            $table->unique(['person_id', 'external_ref']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('person_assessments', 'external_ref')) {
            return;
        }

        Schema::table('person_assessments', function (Blueprint $table): void {
            $table->dropUnique(['person_id', 'external_ref']);
            $table->dropColumn('external_ref');
        });
    }
};
