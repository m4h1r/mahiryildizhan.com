<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sync v2 (S2): optimistic-concurrency version counter on every synced model.
 * Starts at 1 and is bumped by the Syncable trait on each update; Alice sends it
 * back as `If-Match` so a stale write is rejected with 412.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = ['people', 'person_assessments', 'todo_items'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'version')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t): void {
                $t->unsignedInteger('version')->default(1)->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'version')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t): void {
                $t->dropColumn('version');
            });
        }
    }
};
