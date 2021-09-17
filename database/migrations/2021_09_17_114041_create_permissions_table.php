<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            ['name'=>'create user'], ['name'=>'retrieve user'], ['name'=>'update user'], ['name'=>'delete user'], ['name'=>'create permission'],['name'=>'retrieve permission'],['name'=>'update permission'],['name'=>'delete permission'],
            ['name'=>'assign permission'],['name'=>'create role'],['name'=>'retrieve role'],['name'=>'update role'],['name'=>'delete role']
        ]);

      
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
