<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name')->unique();
            $table->string('device_ip', 45)->nullable();
            $table->unsignedInteger('device_port')->default(4370);
            $table->string('device_model')->nullable();
            $table->string('manufacturer')->default('ZKSoftware');
            $table->string('serial_number')->nullable()->unique();
            $table->string('mac_address', 17)->nullable();
            $table->string('location')->nullable();
            $table->string('timezone', 64)->default('America/Caracas');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync')->default(false);
            $table->unsignedInteger('sync_interval')->default(60);
            $table->unsignedInteger('max_connections')->default(5);
            $table->unsignedInteger('connection_timeout')->default(5);
            $table->string('connection_type')->default('TCP/IP');
            $table->string('protocol')->default('ZKProtocol');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->json('additional_config')->nullable();
            $table->json('capabilities')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->timestamp('last_connection_test')->nullable();
            $table->string('connection_status')->default('disconnected');
            $table->text('connection_error')->nullable();
            $table->timestamps();
            $table->index(['device_ip', 'device_port']);
            $table->index(['manufacturer', 'device_model']);
            $table->index('connection_status');
        });
    }

    public function down(): void { Schema::dropIfExists('biometric_devices'); }
};
