<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //nullable in joining,designation & salary will be removed
        // Schema::create('employees', function (Blueprint $table) {
        //     $table->id();
        //     $table->dateTime('joining')->nullable();
        //     $table->string('designation')->nullable();
        //     $table->float('salary')->nullable();
        //     $table->enum('status', ['free', 'busy']);
        //     $table->foreignId('user_id')->constrained('users');
        //     $table->foreignId('created_by')->constrained('users');
        //     $table->foreignId('updated_by')->nullable()->constrained('users');
        //     $table->softDeletes();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
