<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('roles', 'label')) {
                $table->string('label')->nullable()->after('name');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('permissions', 'label')) {
                $table->string('label')->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'amministratore')) {
                $table->boolean('amministratore')->default(false)->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'amministratore')) {
                $table->dropColumn('amministratore');
            }
            if (Schema::hasColumn('permissions', 'label')) {
                $table->dropColumn('label');
            }
            if (Schema::hasColumn('permissions', 'name')) {
                $table->dropColumn('name');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'label')) {
                $table->dropColumn('label');
            }
            if (Schema::hasColumn('roles', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
