<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('role_user') || !Schema::hasTable('user')) {
            return;
        }

        $fk = DB::table('information_schema.key_column_usage')
            ->select('constraint_name', 'referenced_table_name')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'role_user')
            ->where('column_name', 'user_id')
            ->whereNotNull('referenced_table_name')
            ->first();

        if (!$fk || $fk->referenced_table_name === 'user') {
            return;
        }

        Schema::table('role_user', function (Blueprint $table) use ($fk) {
            $table->dropForeign($fk->constraint_name);
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_role_user_user')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('role_user') || !Schema::hasTable('users')) {
            return;
        }

        $fk = DB::table('information_schema.key_column_usage')
            ->select('constraint_name', 'referenced_table_name')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'role_user')
            ->where('column_name', 'user_id')
            ->whereNotNull('referenced_table_name')
            ->first();

        if (!$fk || $fk->referenced_table_name === 'users') {
            return;
        }

        Schema::table('role_user', function (Blueprint $table) use ($fk) {
            $table->dropForeign($fk->constraint_name);
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_role_user_user')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
