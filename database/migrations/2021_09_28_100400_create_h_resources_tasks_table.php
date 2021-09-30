<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHResourcesTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h_resources_tasks', function (Blueprint $table) {
            $table->id();

            // $table->integer('id',true);
            $table->foreignId('task_id')->constrained('tasks');
            $table->foreignId('resource_id')->constrained('users');
            $table->string('sequence');
            $table->string('tag');
            $table->enum('status', ['pending', 'complete', 'notAssign']);
            //composite key
            // $table->unique(['task_id','resource_id']);
            // $table->primary(['task_id', 'resource_id']);
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
        Schema::dropIfExists('h_resources_tasks');
    }
}
