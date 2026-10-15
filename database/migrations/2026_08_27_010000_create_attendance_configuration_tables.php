<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['team_id', 'name']);
        });

        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_working_day')->default(true);
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->unsignedSmallInteger('expected_minutes')->default(480);
            $table->timestamps();
            $table->unique(['work_schedule_id', 'day_of_week']);
        });

        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'starts_on', 'ends_on']);
        });

        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(10);
            $table->unsignedSmallInteger('early_departure_tolerance_minutes')->default(5);
            $table->unsignedSmallInteger('minimum_daily_minutes')->default(480);
            $table->unsignedSmallInteger('overtime_after_minutes')->default(480);
            $table->boolean('round_clock_times')->default(false);
            $table->unsignedTinyInteger('rounding_interval')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('work_schedule_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('exception_date');
            $table->boolean('is_working_day')->default(false);
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'exception_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
        Schema::dropIfExists('attendance_rules');
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('work_schedule_days');
        Schema::dropIfExists('work_schedules');
    }
};
