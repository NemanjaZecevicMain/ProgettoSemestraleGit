<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classroom_teacher')) {
            Schema::create('classroom_teacher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained('classroom')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('user')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['classroom_id', 'teacher_id'], 'classroom_teacher_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_teacher');
    }
};
