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
        Schema::create('organizational_area_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizational_area_id')->constrained('organizational_areas')->comment('ID de la tabla organizational_areas');
            $table->foreignId('employee_id')->constrained('employees')->comment('ID de la tabla employees');
            $table->date('start_date')->comment('Fecha de inicio de la relación');
            $table->date('end_date')->nullable()->comment('Fecha fin de la relación');
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
        Schema::dropIfExists('organizational_area_people');
    }
};
