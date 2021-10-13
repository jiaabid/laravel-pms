<?php

namespace App\Http\Controllers;

// use App\Models\Role;

use App\Models\Roles;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
// use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RoleController extends Controller
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

            if (auth()->user()->can('retrieve role')) {
                $roles = Role::all();
                if ($roles) {
                    return $this->success_response( [
                        
                        'payload' => $roles->toArray(),
                        'user' => auth()->user()
                    ], 200);
                } else {
                    return $this->error_response("Not found",404);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }

    public function get_roles()
    {
        // $children = Role::with('children')->get();
        $children = Roles::where('parent', 1)->with('children')->get();
        // dd($children);
        // return $children;
        return $children;
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

            if (auth()->user()->can('create role')) {
                $this->validate($request, [
                    'name' => 'required'
                ]);
                $role = new Role();
                $role->fill($request->all());
                $role['created_by'] = auth()->user()->id;
                if ($role->save()) {
                    return $this->success_response( $role, 201);

                } else {
                    return $this->error_response( "Error in saving the role", 400);

                 
                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
        try {

            $role = Role::find(auth()->user()->role_id);
            if ($role) {
                return $this->success_response( $role, 200);
            } else {
                return $this->error_response( "Not found", 404);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
        try {
            if (auth()->user()->can('edit role')) {
                $exist = Role::find($id);
                if (!$exist) {
                    return $this->error_response( "Not found", 404);

                }

                $exist = $exist->fill($request->all());
                $exist['updated_by'] = auth()->user()->id;
                if ($exist->save()) {
                    return $this->success_response( $exist, 200);

                } else {
                    return $this->error_response( "Error in saving ,bad request", 400);

                  
                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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

            if (auth()->user()->can('delete role')) {
                $exist = Roles::find($id);
                if (!$exist) {
                    return $this->error_response( "Not found", 404);

                }
                if ($exist->delete()) {
                    return $this->success_response( [], 204);

                } else {
                    return $this->error_response( "Error in deleting", 400);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }
}
