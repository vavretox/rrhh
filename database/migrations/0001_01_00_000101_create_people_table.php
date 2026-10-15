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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pension_fund_manager_id')->constrained('pension_fund_managers');
            $table->integer('unique_affiliate_number')->comment('Número de afiliado único (NUA)');
            $table->integer('identity_card')->comment('Número de cédula de identidad (DOC)');
            $table->string('first_name')->comment('Primer nombre (NO1)');
            $table->string('middle_name')->nullable()->comment('Segundo nombre (NO2)');
            $table->string('first_surname')->nullable()->comment('Primer apellido (AP1)');
            $table->string('second_surname')->nullable()->comment('Segundo apellido (AP2)');
            $table->string('married_name')->nullable()->comment('Apellido de casada (AP3)');
            $table->enum('gender', ['M', 'F'])->default('M')->comment('Sexo (SEX)');
            $table->date('date_of_birth')->comment('Fecha de nacimiento (NAC)');
            $table->string('military_service_booklet_number')->nullable()->comment('Número de libreta militar (LIB)');
            $table->string('social_security_affiliation_code')->nullable()->comment('Número de afiliación al seguro social (REN)');
            $table->string('identification_document_plugin')->nullable()->comment('Complemento del documento de identidad (NCO)');
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
        Schema::dropIfExists('people');
    }
};
