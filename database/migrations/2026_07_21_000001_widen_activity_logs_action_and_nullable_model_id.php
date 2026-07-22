<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->string('action', 20)->change();
                $table->unsignedBigInteger('model_id')->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE activity_logs MODIFY action VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY model_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->enum('action', ['created', 'updated', 'deleted'])->change();
                $table->unsignedBigInteger('model_id')->nullable(false)->change();
            });

            return;
        }

        DB::statement("ALTER TABLE activity_logs MODIFY action ENUM('created', 'updated', 'deleted') NOT NULL");
        DB::statement('ALTER TABLE activity_logs MODIFY model_id BIGINT UNSIGNED NOT NULL');
    }
};
