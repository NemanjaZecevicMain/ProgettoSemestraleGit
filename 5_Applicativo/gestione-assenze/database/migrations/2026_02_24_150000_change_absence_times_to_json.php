<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE absence MODIFY time_from JSON NULL");
        DB::statement("ALTER TABLE absence MODIFY time_to JSON NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE absence MODIFY time_from VARCHAR(20) NULL");
        DB::statement("ALTER TABLE absence MODIFY time_to VARCHAR(20) NULL");
    }
};
