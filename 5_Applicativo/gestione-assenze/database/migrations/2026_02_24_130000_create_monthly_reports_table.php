<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('monthly_report')) {
            Schema::create('monthly_report', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('user')->cascadeOnDelete();
                $table->string('month', 7);
                $table->string('file_path');
                $table->dateTime('generated_at')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'month'], 'monthly_report_student_month_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_report');
    }
};
