<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        $driver = DB::getDriverName();
        $roles = "'STUDENT','TEACHER','ADMIN','CAPOLAB','DIREZIONE'";

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `user` MODIFY `role` ENUM($roles) NOT NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `user` MODIFY `role` VARCHAR(255) NOT NULL");
        }
    }
};
