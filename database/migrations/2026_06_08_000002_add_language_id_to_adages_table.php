<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('adages', 'language_id')) {
            Schema::table('adages', function (Blueprint $table) {
                $table->foreignId('language_id')->nullable()->after('keywords')
                    ->constrained('post_languages')->nullOnDelete();
            });
        }

        // Best-effort: match existing language strings to post_languages by code or name
        if (Schema::hasColumn('adages', 'language')) {
            DB::table('adages')->whereNotNull('language')->where('language', '!=', '')->orderBy('id')->each(function ($adage) {
                $lang = DB::table('post_languages')
                    ->where(DB::raw('LOWER(code)'), strtolower($adage->language))
                    ->orWhere(DB::raw('LOWER(name)'), strtolower($adage->language))
                    ->first();

                if ($lang) {
                    DB::table('adages')->where('id', $adage->id)->update(['language_id' => $lang->id]);
                }
            });

            Schema::table('adages', function (Blueprint $table) {
                $table->dropColumn('language');
            });
        }
    }

    public function down(): void
    {
        Schema::table('adages', function (Blueprint $table) {
            $table->string('language')->nullable()->after('keywords');
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }
};
