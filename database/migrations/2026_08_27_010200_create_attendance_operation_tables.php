<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('biometric_device_id')->nullable()->constrained('biometric_devices')->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->date('work_date');
            $table->dateTime('recorded_at');
            $table->string('type');
            $table->string('source')->default('biometric');
            $table->string('status')->default('valid');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['biometric_device_id', 'external_id']);
            $table->index(['employee_id', 'work_date', 'recorded_at']);
        });

        Schema::create('manual_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->dateTime('recorded_at');
            $table->string('type');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });

        Schema::create('attendance_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('scheduled_days')->default(0);
            $table->unsignedSmallInteger('worked_days')->default(0);
            $table->unsignedSmallInteger('absence_days')->default(0);
            $table->unsignedInteger('worked_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->text('observations')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sheets');
        Schema::dropIfExists('manual_attendances');
        Schema::dropIfExists('attendances');
    }
};
