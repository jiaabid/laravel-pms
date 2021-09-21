<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Permission::insert([
            [
                'name'=>'create user',
                'guard_name'=>'api'
            ],
            [
                'name'=>'retrieve user',
                'guard_name'=>'api'
            ],
            [
                'name'=>'edit user',
                'guard_name'=>'api'
            ],
            [
                'name'=>'delete user',
                'guard_name'=>'api'
            ],
            [
                'name'=>'create role',
                'guard_name'=>'api'
            ],
            [
                'name'=>'retrieve role',
                'guard_name'=>'api'
            ],
            [
                'name'=>'edit role',
                'guard_name'=>'api'
            ],
            [
                'name'=>'delete role',
                'guard_name'=>'api'
            ],
            [
                'name'=>'create permission',
                'guard_name'=>'api'
            ],
            [
                'name'=>'retrieve permission',
                'guard_name'=>'api'
            ],
            [
                'name'=>'edit permission',
                'guard_name'=>'api'
            ],
            [
                'name'=>'delete permission',
                'guard_name'=>'api'
            ]
            ]);

            $role = Role::where('name','superadmin')->first();
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
            $user = User::where('email','admin@outcastsolutions.us')->first();
            $user->assignRole($role);
    }
}
