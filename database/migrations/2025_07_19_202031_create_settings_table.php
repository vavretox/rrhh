<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('national_minimum_wage')->comment('Salario minimo nacional');
            $table->string('description')->nullable()->comment('Descripcion del salario minimo nacional.');
            $table->date('start_date')->comment("Fecha de inicio del salario minimo nacional");
            $table->date('end_date')->nullable()->comment("Fecha de fin del salario minimo nacional");
            $table->enum('state', ['activo', 'inactivo'])->default('activo');
            $table->uuid('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
