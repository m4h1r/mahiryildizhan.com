<?php

namespace Database\Seeders;

use App\Models\BloodType;
use App\Models\Currency;
use App\Models\ExpenseType;
use App\Models\Gender;
use App\Models\HairColor;
use App\Models\IncomeSource;
use App\Models\IncomeType;
use App\Models\InteractionType;
use App\Models\PostCategory;
use App\Models\PostLanguage;
use App\Models\Sector;
use App\Models\TaxOffice;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGenders();
        $this->seedBloodTypes();
        $this->seedHairColors();
        $this->seedPostLanguages();
        $this->seedPostCategories();
        $this->seedIncomeSources();
        $this->seedIncomeTypes();
        $this->seedExpenseTypes();
        $this->seedCurrencies();
        $this->seedInteractionTypes();
        $this->seedSectors();
        $this->seedTaxOffices();
    }

    private function seedGenders(): void
    {
        foreach ([
            ['name' => 'Erkek', 'slug' => 'erkek'],
            ['name' => 'Kadin', 'slug' => 'kadin'],
            ['name' => 'Diger', 'slug' => 'diger'],
        ] as $row) {
            Gender::query()->updateOrCreate(['name' => $row['name']], $row);
        }
    }

    private function seedBloodTypes(): void
    {
        foreach (['A Rh+', 'A Rh-', 'B Rh+', 'B Rh-', 'AB Rh+', 'AB Rh-', '0 Rh+', '0 Rh-'] as $name) {
            BloodType::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }

    private function seedHairColors(): void
    {
        foreach ([
            ['name' => 'Siyah', 'slug' => 'siyah'],
            ['name' => 'Kahverengi', 'slug' => 'kahverengi'],
            ['name' => 'Sari', 'slug' => 'sari'],
            ['name' => 'Kizil', 'slug' => 'kizil'],
        ] as $row) {
            HairColor::query()->updateOrCreate(['name' => $row['name']], $row);
        }
    }

    private function seedPostLanguages(): void
    {
        foreach ([
            ['name' => 'Turkce', 'code' => 'tr'],
            ['name' => 'English', 'code' => 'en'],
        ] as $row) {
            PostLanguage::query()->updateOrCreate(['code' => $row['code']], $row);
        }
    }

    private function seedPostCategories(): void
    {
        foreach ([
            ['name' => 'Genel', 'slug' => 'genel'],
            ['name' => 'Teknoloji', 'slug' => 'teknoloji'],
            ['name' => 'Kisisel', 'slug' => 'kisisel'],
        ] as $row) {
            PostCategory::query()->updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    private function seedIncomeSources(): void
    {
        foreach (['Maas', 'Proje', 'Yatirim', 'Diger'] as $name) {
            IncomeSource::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }

    private function seedIncomeTypes(): void
    {
        foreach (['Tek Seferlik', 'Duzensiz', 'Duzenli'] as $name) {
            IncomeType::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }

    private function seedExpenseTypes(): void
    {
        $expenseTypes = [
            ['name' => 'Fuel', 'government_acceptance_percentage' => 70],
            ['name' => 'Meal', 'government_acceptance_percentage' => 100],
            ['name' => 'Gida', 'government_acceptance_percentage' => 100],
            ['name' => 'Ulasim', 'government_acceptance_percentage' => 100],
            ['name' => 'Konaklama', 'government_acceptance_percentage' => 100],
            ['name' => 'Yazilim', 'government_acceptance_percentage' => 100],
            ['name' => 'Diger', 'government_acceptance_percentage' => 100],
        ];

        foreach ($expenseTypes as $row) {
            ExpenseType::query()->updateOrCreate(['name' => $row['name']], $row);
        }
    }

    private function seedCurrencies(): void
    {
        foreach ([
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
        ] as $row) {
            Currency::query()->updateOrCreate(['code' => $row['code']], $row);
        }
    }

    private function seedInteractionTypes(): void
    {
        foreach (['Toplanti', 'Telefon', 'E-posta', 'Diger'] as $name) {
            InteractionType::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }

    private function seedSectors(): void
    {
        foreach ([
            'Akaryakit', 'Bilisim', 'Danismanlik', 'Egitim', 'Eglence', 'Gida',
            'Insaat', 'Lojistik', 'Perakende', 'Reklam', 'Saglik', 'Tekstil',
            'Turizm', 'Uretim', 'Diger',
        ] as $name) {
            Sector::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }

    private function seedTaxOffices(): void
    {
        foreach ([
            'Alemdar', 'Kadikoy', 'Besiktas', 'Sisli', 'Uskudar',
            'Bakirkoy', 'Marmara Kurumlar', 'Boğaziçi Kurumlar',
        ] as $name) {
            TaxOffice::query()->updateOrCreate(['name' => $name], ['name' => $name]);
        }
    }
}
