<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE features MODIFY COLUMN page_type ENUM('none','beranda','onsite','real','3d','book','slideshow','profile') DEFAULT 'none'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE features MODIFY COLUMN page_type ENUM('none','beranda','onsite','real','3d','book','slideshow') DEFAULT 'none'");
    }
};
