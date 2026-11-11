<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('second_surname');

            // Birth date in parts: often only day + month (or only the year) is known.
            // `birthday` stays as the full date and is derived when all three exist.
            $table->unsignedTinyInteger('birth_day')->nullable()->after('birthday');
            $table->unsignedTinyInteger('birth_month')->nullable()->after('birth_day');
            $table->unsignedSmallInteger('birth_year')->nullable()->after('birth_month');

            $table->string('relationship_scope', 20)->nullable()->index()->after('address');
            $table->string('relationship_label')->nullable()->after('relationship_scope');
            $table->json('relationship_bases')->nullable()->after('relationship_label');

            $table->string('company')->nullable()->after('relationship_bases');
            $table->string('job_title')->nullable()->after('company');
            $table->foreignId('stakeholder_id')->nullable()->after('job_title')
                ->constrained('stakeholders')->nullOnDelete();

            $table->text('character_summary')->nullable()->after('stakeholder_id');
            $table->json('interests')->nullable()->after('character_summary');
            $table->json('hobbies')->nullable()->after('interests');
            $table->json('tendencies')->nullable()->after('hobbies');

            $table->date('met_at')->nullable()->after('tendencies');
            $table->string('met_how')->nullable()->after('met_at');

            $table->string('alice_slug')->nullable()->unique()->after('met_how');
        });

        // Backfill the parts from existing full birthdays (DB-agnostic).
        DB::table('people')->whereNotNull('birthday')->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $date = Carbon::parse($row->birthday);
                    DB::table('people')->where('id', $row->id)->update([
                        'birth_day' => $date->day,
                        'birth_month' => $date->month,
                        'birth_year' => $date->year,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stakeholder_id');
            $table->dropUnique(['alice_slug']);
            $table->dropIndex(['relationship_scope']);
            $table->dropColumn([
                'nickname', 'birth_day', 'birth_month', 'birth_year',
                'relationship_scope', 'relationship_label', 'relationship_bases',
                'company', 'job_title', 'character_summary', 'interests', 'hobbies',
                'tendencies', 'met_at', 'met_how', 'alice_slug',
            ]);
        });
    }
};
