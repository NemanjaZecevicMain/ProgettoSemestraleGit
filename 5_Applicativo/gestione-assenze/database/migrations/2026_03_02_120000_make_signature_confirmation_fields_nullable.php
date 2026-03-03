<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('signature_confirmation')) {
            Schema::table('signature_confirmation', function (Blueprint $table) {
                $table->string('signer_name')->nullable()->change();
                $table->string('signer_email')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('signature_confirmation')) {
            Schema::table('signature_confirmation', function (Blueprint $table) {
                $table->string('signer_name')->nullable(false)->change();
                $table->string('signer_email')->nullable(false)->change();
            });
        }
    }
};
