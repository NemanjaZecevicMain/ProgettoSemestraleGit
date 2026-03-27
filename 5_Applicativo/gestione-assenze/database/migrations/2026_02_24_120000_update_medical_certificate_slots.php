<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = $this->indexExists('medical_certificate', 'medical_certificate_absence_id_unique');

        Schema::table('medical_certificate', function (Blueprint $table) use ($indexExists) {
            if ($indexExists) {
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

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $index = DB::selectOne(
                'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ? LIMIT 1',
                [$table, $indexName]
            );

            return (bool) $index;
        }

        if ($driver === 'sqlsrv') {
            $index = DB::selectOne(
                'SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID(?) AND name = ?',
                [$table, $indexName]
            );

            return (bool) $index;
        }

        $index = DB::selectOne(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$indexName]
        );

        return (bool) $index;
    }
};
