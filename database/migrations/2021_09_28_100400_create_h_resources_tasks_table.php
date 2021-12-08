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
        Schema::create('resources_tasks', function (Blueprint $table) {
            $table->id();

            // $table->integer('id',true);
            $table->foreignId('task_id')->constrained('tasks');
            $table->foreignId('resource_id')->constrained('users');
            $table->integer('sequence');
            $table->bigInteger('tag_id');
            $table->unsignedBigInteger('status');
            $table->float('estimated_effort');
            $table->float('total_effort')->nullable();
            $table->boolean('pause')->default(false);
            $table->boolean('delay')->default(false);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
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
        Schema::dropIfExists('resources_tasks');
    }
}
