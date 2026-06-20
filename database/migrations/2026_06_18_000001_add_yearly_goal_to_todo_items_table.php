<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->string('yearly_goal', 10)->default('NA')->after('is_bucketlist');
        });
    }

    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn('yearly_goal');
        });
    }
};
