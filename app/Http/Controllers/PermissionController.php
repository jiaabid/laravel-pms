<?php

namespace App\Http\Controllers;

// use App\Models\Permission;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Traits\ResponseTrait;
use App\Models\RolePermission;
use App\Models\Roles;

class PermissionController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (auth()->user()->can('retrieve permission')) {
            if($request->query("all") == "true"){
                $permissions = Permission::all();
            }else{
                $permissions = Permission::paginate(12);
            }
         
            if (!$permissions) {
                return $this->error_response("Not found", 404);
            }
            return $this->success_response($permissions, 200);
        } else {
            return $this->success_response(auth()->user()->getAllPermissions(), 200);
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (auth()->user()->can('create permission')) {
            $this->validate($request, [

                'name' => 'required'
            ]);

            $permission = Permission::create([
                'name' => $request->name,
                'created_by' => auth()->user()->id
            ]);

            if ($permission) {
                return $this->success_response($permission, 201);
            } else {
                return $this->error_response("Error in creating permission", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = User::find($id);
        $permissions = collect($user->getAllPermissions());
        // return gettype($permissions);
        // $permissions = $permissions->pluck('id','name');
        return $this->success_response($permissions, 200);
        // return response()->json([
        //     'permissions'=>$user->getAllPermissions()
        // ]);

    }
    //permission by role

    public function role_permissions($id){
        $role = Role::find($id);
        if($role){
            return $this->success_response($role->permissions,200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (auth()->user()->can('edit permission')) {
            $this->validate($request, [

                'name' => 'required'
            ]);

            $permission = Permission::find($id);
            $permission->fill($request->all());
            if ($permission->save()) {
                return $this->success_response($permission, 200);
            } else {
                return $this->error_response("Error in updating permission", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (auth()->user()->can('delete permission')) {
            $exist = Permission::find($id);
            if (!$exist) {
                return $this->error_response("Not found", 404);
            }
            if ($exist->delete()) {
                return $this->success_response($exist, 200);
            } else {
                return $this->error_response("Error in deleting", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }


    /**
     * assign_permissions to the specified roles 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function assign_permission(Request $request)
    {

        if (auth()->user()->can('assign permission')) {
            $this->validate($request, [

                'role_id' => 'required',
                'permissions' => 'required'
            ]);

            $role = Role::find($request->role_id);
            $permissions = $role->getAllPermissions();

            $role->givePermissionTo($request->permissions);

            foreach ($request->permissions as $permission) {
                $role->givePermissionTo($permission);
            }
            return $this->success_response(['msg' => "permissions assigned"], 200);
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    //remove permissions
    public function remove_permission(Request $request)
    {
        if (auth()->user()->can('assign permission')) { 
            $this->validate($request, [

                'role_id' => 'required',
                'permissions' => 'required'
            ]);
            $role = Role::find($request->role_id);
            foreach($request->permissions as $permission){
                $role->revokePermissionTo($permission);
            };
            // RolePermission::where('role_id', $request->role_id)->whereIn('permission_id', $request->permissions)->delete();


            return $this->success_response(['msg' => "permissions removed"], 200);
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }
}
