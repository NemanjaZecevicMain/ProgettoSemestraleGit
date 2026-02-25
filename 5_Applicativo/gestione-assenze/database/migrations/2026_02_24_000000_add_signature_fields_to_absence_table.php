<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            if (!Schema::hasColumn('absence', 'is_signed')) {
                $table->boolean('is_signed')->default(false)->after('is_approved');
            }
            if (!Schema::hasColumn('absence', 'signed_at')) {
                $table->dateTime('signed_at')->nullable()->after('is_signed');
            }
            if (!Schema::hasColumn('absence', 'signed_by_user_id')) {
                $table->foreignId('signed_by_user_id')->nullable()->after('signed_at')->constrained('user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absence', function (Blueprint $table) {
            if (Schema::hasColumn('absence', 'signed_by_user_id')) {
                $table->dropConstrainedForeignId('signed_by_user_id');
            }
            if (Schema::hasColumn('absence', 'signed_at')) {
                $table->dropColumn('signed_at');
            }
            if (Schema::hasColumn('absence', 'is_signed')) {
                $table->dropColumn('is_signed');
            }
        });
    }
};
