<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biometric_device_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer');
            $table->string('model');
            $table->string('protocol');
            $table->json('default_ports')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('commands')->nullable();
            $table->json('config_parameters')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['manufacturer', 'model']);
            $table->index(['manufacturer', 'protocol']);
        });
    }

    public function down(): void { Schema::dropIfExists('biometric_device_profiles'); }
};
