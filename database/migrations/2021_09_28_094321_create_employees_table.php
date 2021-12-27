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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->dateTime('joining_date')->nullable();
            $table->string('contact',11)->nullable();
            $table->string('designation')->nullable();
            $table->double('salary')->nullable();
            $table->time('duty_start');
            $table->time('duty_end');
            $table->float('working_hrs');
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->boolean('break')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
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
