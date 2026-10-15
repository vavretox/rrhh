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
        Schema::create('employee_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->comment('Llave foranea a la tabla employees');
            $table->foreignId('form_id')->constrained('forms')->comment('Llave foranea a la tabla forms');
            $table->foreignId('sheet_id')->constrained('sheets')->comment('Llave foranea a la tabla sheet');
            $table->integer('worked_days')->comment('Cantidad de dias trabajados en el mes');
            $table->float('border_bonus', 2)->nullable()->comment('Bono de frontera');
            $table->float('iva', 2)->nullable()->comment('Impuesto al valor agregado');
            $table->float('violations_and_fines', 2)->nullable()->comment('Faltas y multas');
            $table->float('judicial_withholdings', 2)->nullable()->comment('Retenciones judiciales');
            $table->float('other_discounts', 2)->nullable()->comment('Retenciones judiciales');
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
        Schema::dropIfExists('employee_forms');
    }
};
