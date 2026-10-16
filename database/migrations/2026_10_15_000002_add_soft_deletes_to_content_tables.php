<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SoftDeletes on admin-CRUD content models (L2). Subscriber (status lifecycle:
 * active/unsubscribed), Media (file lifecycle) and PostCategory (a dictionary
 * table managed via the generic hard-delete dictionary CRUD) deliberately stay
 * hard-delete — see project-standards.md.
 */
return new class extends Migration
{
    private array $tables = ['adages', 'links', 'timeline_events'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (! Schema::hasColumn($t->getTable(), 'deleted_at')) {
                    $t->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (Schema::hasColumn($t->getTable(), 'deleted_at')) {
                    $t->dropSoftDeletes();
                }
            });
        }
    }
};
