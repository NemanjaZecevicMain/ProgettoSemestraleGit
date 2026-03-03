<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        Schema::table('user', function (Blueprint $table) {
            if (!Schema::hasColumn('user', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('email');
            }

            if (!Schema::hasColumn('user', 'guardian_id')) {
                $table->foreignId('guardian_id')->nullable()->after('classroom_id')->constrained('user')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'guardian_id')) {
                $table->dropConstrainedForeignId('guardian_id');
            }

            if (Schema::hasColumn('user', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
        });
    }
};
