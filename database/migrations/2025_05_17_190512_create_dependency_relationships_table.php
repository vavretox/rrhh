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
        Schema::create('dependency_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('code')->comment('Tipo de relación de dependencia del funcionario (TIP)');
            $table->string('description')->comment('Descripción de la relación de dependencia del funcionario');
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
        Schema::dropIfExists('dependency_relationships');
    }
};
