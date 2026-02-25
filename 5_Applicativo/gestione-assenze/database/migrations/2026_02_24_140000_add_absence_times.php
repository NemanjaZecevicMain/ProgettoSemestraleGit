<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            if (!Schema::hasColumn('absence', 'time_from')) {
                $table->time('time_from')->nullable()->after('date_to');
            }
            if (!Schema::hasColumn('absence', 'time_to')) {
                $table->time('time_to')->nullable()->after('time_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            if (Schema::hasColumn('absence', 'time_to')) {
                $table->dropColumn('time_to');
            }
            if (Schema::hasColumn('absence', 'time_from')) {
                $table->dropColumn('time_from');
            }
        });
    }
};
