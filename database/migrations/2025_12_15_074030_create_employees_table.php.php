<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('firstName');
            $table->string('lastName');
            $table->string('middleName')->nullable();
            $table->string('suffix')->nullable();
            
            // Contact Information
            $table->string('email')->unique();
            $table->string('contact')->nullable();
            
            // Work Information
            $table->string('position');
            $table->string('department');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('employeeType')->nullable();
            $table->string('employee_type')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            
            // Personal Details
            $table->string('sex')->nullable();
            $table->integer('age')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('citizenship')->nullable();
            
            // Address
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('brgy')->nullable();
            $table->string('zipcode')->nullable();
            
            // Government IDs
            $table->string('pag_ibig')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('tin')->nullable();
            $table->string('landbank')->nullable();
            $table->string('gsis')->nullable();
            
            // Emergency & Profile
            $table->string('incase_of_emergency')->nullable();
            $table->string('avatar')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};