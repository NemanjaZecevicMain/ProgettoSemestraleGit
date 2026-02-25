<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $index = DB::select(
            "SHOW INDEX FROM medical_certificate WHERE Key_name = ?",
            ['medical_certificate_absence_id_unique']
        );

        Schema::table('medical_certificate', function (Blueprint $table) use ($index) {
            if ($index) {
                $table->dropUnique('medical_certificate_absence_id_unique');
            }
            if (!Schema::hasColumn('medical_certificate', 'slot')) {
                $table->unsignedTinyInteger('slot')->default(1)->after('absence_id');
                $table->unique(['absence_id', 'slot'], 'medical_certificate_absence_slot_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_certificate', function (Blueprint $table) {
            if (Schema::hasColumn('medical_certificate', 'slot')) {
                $table->dropUnique('medical_certificate_absence_slot_unique');
                $table->dropColumn('slot');
            }
            if (Schema::hasColumn('medical_certificate', 'absence_id')) {
                $table->unique('absence_id', 'medical_certificate_absence_id_unique');
            }
        });
    }
};
