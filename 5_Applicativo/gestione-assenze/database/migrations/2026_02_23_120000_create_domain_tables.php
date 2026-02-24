<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classroom')) {
            Schema::create('classroom', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('year');
                $table->string('section');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->nullable()->constrained('classroom');
                $table->string('name');
                $table->string('email')->unique();
                $table->text('description')->nullable();
                $table->string('password_hash');
                $table->string('role');
                $table->boolean('is_minor')->default(false);
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
                $table->primary(['role_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('absence')) {
            Schema::create('absence', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('user')->cascadeOnDelete();
                $table->date('date_from')->nullable();
                $table->date('date_to')->nullable();
                $table->string('reason')->nullable();
                $table->string('status')->nullable();
                $table->unsignedInteger('hours_assigned')->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_approved')->nullable();
                $table->foreignId('approved_by_user_id')->nullable()->constrained('user');
                $table->dateTime('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('medical_certificate')) {
            Schema::create('medical_certificate', function (Blueprint $table) {
                $table->id();
                $table->foreignId('absence_id')->unique()->constrained('absence')->cascadeOnDelete();
                $table->string('file_path');
                $table->dateTime('uploaded_at')->nullable();
                $table->dateTime('deadline_at')->nullable();
                $table->foreignId('validated_by_teacher_id')->nullable()->constrained('user');
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('signature_confirmation')) {
            Schema::create('signature_confirmation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('absence_id')->unique()->constrained('absence')->cascadeOnDelete();
                $table->string('signer_name')->nullable();
                $table->string('signer_email')->nullable();
                $table->string('token_hash');
                $table->dateTime('expires_at')->nullable();
                $table->dateTime('signed_at')->nullable();
                $table->string('signature_path')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('delay')) {
            Schema::create('delay', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('user')->cascadeOnDelete();
                $table->foreignId('created_by_teacher_id')->nullable()->constrained('user');
                $table->date('date')->nullable();
                $table->unsignedInteger('minutes')->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_signed')->default(false);
                $table->dateTime('signed_at')->nullable();
                $table->foreignId('signed_by_user_id')->nullable()->constrained('user');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('import_batch')) {
            Schema::create('import_batch', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->nullable()->constrained('classroom');
                $table->foreignId('imported_by')->nullable()->constrained('user');
                $table->string('file_name');
                $table->dateTime('imported_at')->nullable();
                $table->string('status')->nullable();
                $table->unsignedInteger('created_users_count')->default(0);
                $table->unsignedInteger('created_classes_count')->default(0);
                $table->text('errors')->nullable();
                $table->dateTime('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('audit_log')) {
            Schema::create('audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('user');
                $table->string('action');
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('metadata')->nullable();
                $table->dateTime('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('absence_log')) {
            Schema::create('absence_log', function (Blueprint $table) {
                $table->id('log_id');
                $table->string('operation');
                $table->dateTime('logged_at');
                $table->foreignId('logged_by_user_id')->nullable()->constrained('user');
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->date('date_from')->nullable();
                $table->date('date_to')->nullable();
                $table->string('reason')->nullable();
                $table->string('status')->nullable();
                $table->unsignedInteger('hours_assigned')->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_approved')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('classroom_log')) {
            Schema::create('classroom_log', function (Blueprint $table) {
                $table->id('log_id');
                $table->string('operation');
                $table->dateTime('logged_at');
                $table->foreignId('logged_by_user_id')->nullable()->constrained('user');
                $table->unsignedBigInteger('id')->nullable();
                $table->string('name')->nullable();
                $table->unsignedInteger('year')->nullable();
                $table->string('section')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('delay_log')) {
            Schema::create('delay_log', function (Blueprint $table) {
                $table->id('log_id');
                $table->string('operation');
                $table->dateTime('logged_at');
                $table->foreignId('logged_by_user_id')->nullable()->constrained('user');
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('created_by_teacher_id')->nullable();
                $table->date('date')->nullable();
                $table->unsignedInteger('minutes')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_log')) {
            Schema::create('user_log', function (Blueprint $table) {
                $table->id('log_id');
                $table->string('operation');
                $table->dateTime('logged_at');
                $table->foreignId('logged_by_user_id')->nullable()->constrained('user');
                $table->unsignedBigInteger('id')->nullable();
                $table->unsignedBigInteger('classroom_id')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('password_hash')->nullable();
                $table->string('role')->nullable();
                $table->boolean('is_minor')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_log');
        Schema::dropIfExists('delay_log');
        Schema::dropIfExists('classroom_log');
        Schema::dropIfExists('absence_log');
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('import_batch');
        Schema::dropIfExists('delay');
        Schema::dropIfExists('signature_confirmation');
        Schema::dropIfExists('medical_certificate');
        Schema::dropIfExists('absence');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('user');
        Schema::dropIfExists('classroom');
    }
};
