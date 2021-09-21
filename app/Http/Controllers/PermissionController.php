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
                return response()->json([
                    'status' => true,
                    'payload' => Permission::all()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
                    return response()->json([
                        'success' => true,
                        'payload' => $permission
                    ],201);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => "Error in creating permission"
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
        // $user = User::find($id);
        // return response()->json([
        //     'permissions'=>$user->getAllPermissions()
        // ]);

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
                    return response()->json([
                        'success' => true,
                        'payload' => $permission
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => "Error in updating permission"
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
                    return response()->json([
                        'success' => false,
                        'error' => "Not found"
                    ], 404);
                }
                if ($exist->delete()) {
                    return response()->json([
                        "success" => true
                    ]);
                } else {
                    return response()->json([
                        "success" => false,
                        'error' => 'Error in delete'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


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
                return response()->json([
                    'success' => true,
                    'payload' => ['msg' => "permissions assigned"]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
