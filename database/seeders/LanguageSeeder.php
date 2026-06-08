<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'af', 'name' => 'Afrikaans'],
            ['code' => 'sq', 'name' => 'Albanian'],
            ['code' => 'ar', 'name' => 'Arabic'],
            ['code' => 'hy', 'name' => 'Armenian'],
            ['code' => 'az', 'name' => 'Azerbaijani'],
            ['code' => 'eu', 'name' => 'Basque'],
            ['code' => 'be', 'name' => 'Belarusian'],
            ['code' => 'bn', 'name' => 'Bengali'],
            ['code' => 'bs', 'name' => 'Bosnian'],
            ['code' => 'bg', 'name' => 'Bulgarian'],
            ['code' => 'ca', 'name' => 'Catalan'],
            ['code' => 'zh', 'name' => 'Chinese'],
            ['code' => 'hr', 'name' => 'Croatian'],
            ['code' => 'cs', 'name' => 'Czech'],
            ['code' => 'da', 'name' => 'Danish'],
            ['code' => 'nl', 'name' => 'Dutch'],
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'eo', 'name' => 'Esperanto'],
            ['code' => 'et', 'name' => 'Estonian'],
            ['code' => 'fi', 'name' => 'Finnish'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'gl', 'name' => 'Galician'],
            ['code' => 'ka', 'name' => 'Georgian'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'el', 'name' => 'Greek'],
            ['code' => 'gu', 'name' => 'Gujarati'],
            ['code' => 'he', 'name' => 'Hebrew'],
            ['code' => 'hi', 'name' => 'Hindi'],
            ['code' => 'hu', 'name' => 'Hungarian'],
            ['code' => 'is', 'name' => 'Icelandic'],
            ['code' => 'id', 'name' => 'Indonesian'],
            ['code' => 'ga', 'name' => 'Irish'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'ja', 'name' => 'Japanese'],
            ['code' => 'kn', 'name' => 'Kannada'],
            ['code' => 'kk', 'name' => 'Kazakh'],
            ['code' => 'ko', 'name' => 'Korean'],
            ['code' => 'ku', 'name' => 'Kurdish'],
            ['code' => 'ky', 'name' => 'Kyrgyz'],
            ['code' => 'lv', 'name' => 'Latvian'],
            ['code' => 'lt', 'name' => 'Lithuanian'],
            ['code' => 'lb', 'name' => 'Luxembourgish'],
            ['code' => 'mk', 'name' => 'Macedonian'],
            ['code' => 'ms', 'name' => 'Malay'],
            ['code' => 'ml', 'name' => 'Malayalam'],
            ['code' => 'mt', 'name' => 'Maltese'],
            ['code' => 'mr', 'name' => 'Marathi'],
            ['code' => 'mn', 'name' => 'Mongolian'],
            ['code' => 'ne', 'name' => 'Nepali'],
            ['code' => 'no', 'name' => 'Norwegian'],
            ['code' => 'fa', 'name' => 'Persian'],
            ['code' => 'pl', 'name' => 'Polish'],
            ['code' => 'pt', 'name' => 'Portuguese'],
            ['code' => 'pa', 'name' => 'Punjabi'],
            ['code' => 'ro', 'name' => 'Romanian'],
            ['code' => 'ru', 'name' => 'Russian'],
            ['code' => 'sr', 'name' => 'Serbian'],
            ['code' => 'si', 'name' => 'Sinhala'],
            ['code' => 'sk', 'name' => 'Slovak'],
            ['code' => 'sl', 'name' => 'Slovenian'],
            ['code' => 'so', 'name' => 'Somali'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'sw', 'name' => 'Swahili'],
            ['code' => 'sv', 'name' => 'Swedish'],
            ['code' => 'tl', 'name' => 'Tagalog'],
            ['code' => 'tg', 'name' => 'Tajik'],
            ['code' => 'ta', 'name' => 'Tamil'],
            ['code' => 'tt', 'name' => 'Tatar'],
            ['code' => 'te', 'name' => 'Telugu'],
            ['code' => 'th', 'name' => 'Thai'],
            ['code' => 'tr', 'name' => 'Turkish'],
            ['code' => 'tk', 'name' => 'Turkmen'],
            ['code' => 'uk', 'name' => 'Ukrainian'],
            ['code' => 'ur', 'name' => 'Urdu'],
            ['code' => 'uz', 'name' => 'Uzbek'],
            ['code' => 'vi', 'name' => 'Vietnamese'],
            ['code' => 'cy', 'name' => 'Welsh'],
            ['code' => 'yi', 'name' => 'Yiddish'],
        ];

        $now = now();

        foreach ($languages as $lang) {
            DB::table('post_languages')->insertOrIgnore([
                'code' => $lang['code'],
                'name' => $lang['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
