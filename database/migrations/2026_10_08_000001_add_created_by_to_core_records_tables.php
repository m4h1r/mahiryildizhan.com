<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'people',
            'posts',
            'comments',
            'expenses',
            'incomes',
            'interactions',
            'nodes',
            'timeline_events',
            'adages',
            'subscribers',
            'purchase_items',
            'todo_items',
            'foods',
            'consumptions',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
                });
            }
        }

        // Alice (AI assistant) user, pinned to id=2. `id` is not fillable, so the
        // previous updateOrCreate(['id'=>2]) silently fell through to id=1 and
        // collided with fixtures that hardcode id=1 — force the key explicitly.
        if (! User::query()->whereKey(2)->exists()) {
            $alice = new User([
                'name' => 'Alice Hermes',
                'email' => 'alice.hermes.__1791661575191306000@local.test',
                'password' => bcrypt('password123'),
                'is_admin' => false,
            ]);
            $alice->id = 2;
            $alice->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'people',
            'posts',
            'comments',
            'expenses',
            'incomes',
            'interactions',
            'nodes',
            'timeline_events',
            'adages',
            'subscribers',
            'purchase_items',
            'todo_items',
            'foods',
            'consumptions',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropConstrainedForeignId('created_by');
                });
            }
        }
    }
};
