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
        Schema::create('charge_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_id')->constrained('charges')->comment('ID de la tabla charges');
            $table->foreignId('employee_id')->constrained('employees')->comment('ID de la tabla employees');
            $table->date('start_date')->comment('Fecha de inicio del cargo');
            $table->date('end_date')->nullable()->comment('Fecha de fin del cargo');
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
        Schema::dropIfExists('charge_employees');
    }
};
