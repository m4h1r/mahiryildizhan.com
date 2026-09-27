<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('urgency')->nullable()->after('time_cost_hours');
            $table->unsignedTinyInteger('importance')->nullable()->after('urgency');
        });
    }

    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn(['urgency', 'importance']);
        });
    }
};