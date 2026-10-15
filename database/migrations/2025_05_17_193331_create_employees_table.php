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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->comment('ID de referencia a tabla people');
            $table->foreignId('dependency_relationship_id')->constrained('dependency_relationships')->comment('ID de referencia a tabla dependency_relationships');
            $table->foreignId('category_id')->constrained('categories')->comment("ID de referencia a la tabla categories");
            $table->foreignId('academic_degree_id')->constrained('academic_degrees')->comment("ID de referencia a la tabla academic_degrees");
            $table->integer('item_number')->comment('Número de ítem del empleado (ITE)');
            $table->date('date_of_entry')->comment('Fecha de ingreso del empleado (ING)');
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
        Schema::dropIfExists('employees');
    }
};
