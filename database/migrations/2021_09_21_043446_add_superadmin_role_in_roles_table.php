<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
class AddSuperadminRoleInRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('roles', function (Blueprint $table) {
        //     //
        // });
        $role = Role::create([
            'name'=>'superadmin',
            'guard_name'=>'api'
        ]);
        $user = User::where('name','superadmin')
        ->update(['role_id'=>$role->id]);
        // $user->role_id = $role->id;
        // $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            //
        });
    }
}
