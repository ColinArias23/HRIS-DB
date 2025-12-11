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
        $table->dropColumn([
            'contact',
            'position',
            'department',
            'gender',
            'address',
            'birthdate',
            'salary',
            'employeeType',
            'image',
        ]);
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('contact')->nullable();
        $table->string('position')->nullable();
        $table->string('department')->nullable();
        $table->string('gender')->nullable();
        $table->string('address')->nullable();
        $table->date('birthdate')->nullable();
        $table->decimal('salary', 10, 2)->nullable();
        $table->string('employeeType')->nullable();
        $table->string('image')->nullable();
    });
}

};
