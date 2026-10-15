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
        Schema::create('organizational_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->comment('ID de la tabla entities');
            $table->foreignId('relationship')->nullable()->constrained('organizational_areas')->comment('ID del padre de la tabla organizational_areas');
            $table->string('name')->comment('Nombre del área organizacional');
            $table->string('description')->comment('Descripcion del área organizacional');
            $table->string('code')->comment('Codigo del área organizacional');
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
        Schema::dropIfExists('organizational_areas');
    }
};
