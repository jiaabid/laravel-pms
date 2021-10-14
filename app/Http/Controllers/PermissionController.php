<?php

namespace App\Http\Controllers;

// use App\Models\Permission;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (auth()->user()->can('retrieve permission')) {
                $permissions = Permission::all();
                if (!$permissions) {
                    return $this->error_response("Not found", 404);
                }
                return $this->success_response($permissions, 200);
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
        try {
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
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
        return response()->json([
            'permissions'=>$user->getAllPermissions()
        ]);

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
        try {
            if (auth()->user()->can('update permission')) {
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
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
        try {

            if (auth()->user()->can('delete permission')) {
                $exist = Permission::find($id);
                if (!$exist) {
                    return $this->error_response("Not found", 404);
                }
                if ($exist->delete()) {
                    return $this->success_response([], 204);
                } else {
                    return $this->error_response("Error in deleting", 400);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
        try {
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
                return $this->success_response(  ['msg' => "permissions assigned"], 200);

               
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }
}
