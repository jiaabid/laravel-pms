<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolepermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rolepermissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('permission_id')->constrained('permissions');
            $table->softDeletes();
            $table->timestamps();
        });


        //get the superadmin role id
        $user = DB::table('users')->where('email', '=', 'admin@outcastsolutions.us')->get();
        echo $user;
        $value = [];
        $permissions = DB::table('permissions')->select('id')->get();
        foreach ($permissions as $permission) {
            array_push($value,array(
                'role_id=>'.$user[0]->role_id,
                'permission_id=>'.$permission->id
            ));

            // array_push($value, array(
            //     $user[0]->role_id,
            //     $permission->id
            // ));
        }
        // print_r($value);
        DB::table('rolepermissions')->insert($value);
        // DB::insert('insert into rolepermissions (role_id,permission_id) values (?,?)', $value);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rolepermissions');
    }
}
