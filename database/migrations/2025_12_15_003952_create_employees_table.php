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
            $table->string('firstName');
            $table->string('middleName')->nullable();
            $table->string('lastName');
            $table->string('suffix')->nullable();
            $table->string('email')->unique();
            $table->string('contact')->nullable();
            $table->string('position');
            $table->string('department');
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->text('address')->nullable();
            $table->date('birthdate')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('employeeType')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};