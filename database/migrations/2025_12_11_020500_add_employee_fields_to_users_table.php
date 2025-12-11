<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('firstName')->nullable();
        $table->string('lastName')->nullable();
        $table->string('middleName')->nullable();
        $table->string('suffix')->nullable();

        $table->string('contact')->nullable();
        $table->string('position')->nullable();
        $table->string('department')->nullable();

        $table->string('gender')->nullable();
        $table->string('status')->default('Inactive'); // Active / Inactive

        $table->string('address')->nullable();
        $table->date('birthdate')->nullable();

        $table->decimal('salary', 10, 2)->nullable();
        $table->string('employeeType')->nullable(); // e.g., Full-time, Part-time

        $table->string('image')->nullable(); // path to uploaded image
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
