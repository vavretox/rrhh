<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biometric_communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->string('command');
            $table->string('status');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('biometric_communication_logs'); }
};
