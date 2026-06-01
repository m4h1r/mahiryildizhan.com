<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // total = price * quantity + tax şeklinde kaydedilmiş kayıtları düzelt.
        // Girilen fiyat zaten KDV dahil olduğundan total = price * quantity olmalı.
        DB::statement('UPDATE expenses SET total = ROUND(price * quantity, 2) WHERE tax != 0');
    }

    public function down(): void
    {
        DB::statement('UPDATE expenses SET total = ROUND(price * quantity + tax, 2) WHERE tax != 0');
    }
};
