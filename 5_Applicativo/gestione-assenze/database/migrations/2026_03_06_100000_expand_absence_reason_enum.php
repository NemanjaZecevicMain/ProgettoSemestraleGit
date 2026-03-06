<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('absence') && Schema::hasColumn('absence', 'reason')) {
            DB::statement("
                ALTER TABLE `absence`
                MODIFY `reason` ENUM(
                    'MALATTIA',
                    'VISITA_MEDICA',
                    'IMPEGNO_FAMIGLIARE',
                    'MOTIVI_PERSONALI',
                    'ALTRO'
                ) NOT NULL
            ");
        }

        if (Schema::hasTable('absence_log') && Schema::hasColumn('absence_log', 'reason')) {
            DB::statement("
                ALTER TABLE `absence_log`
                MODIFY `reason` ENUM(
                    'MALATTIA',
                    'VISITA_MEDICA',
                    'IMPEGNO_FAMIGLIARE',
                    'MOTIVI_PERSONALI',
                    'ALTRO'
                ) NULL
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('absence') && Schema::hasColumn('absence', 'reason')) {
            DB::statement("
                ALTER TABLE `absence`
                MODIFY `reason` ENUM('MALATTIA', 'ALTRO') NOT NULL
            ");
        }

        if (Schema::hasTable('absence_log') && Schema::hasColumn('absence_log', 'reason')) {
            DB::statement("
                ALTER TABLE `absence_log`
                MODIFY `reason` ENUM('MALATTIA', 'ALTRO') NULL
            ");
        }
    }
};
