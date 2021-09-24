<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        try {
            if (auth()->user()->can('retrieve user')) {
                $users = User::all();
                if ($users) {
                    return response()->json([
                        'status' => true,
                        'payload' => $users
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'error' => 'No user exist!'
                    ], 404);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        // try {
        //     if (auth()->user()->can()) {
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'payload' => "Unauthorized!"
        //         ], 401);
        //     }
        // } catch (Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
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
            if (auth()->user()->can('create user')) {
                $this->validate($request, [
                    'name' => "required|min:3",
                    'email' => "required|email",
                    'password' => "required|min:5",
                    'role_id' => 'required',
                    'dept_id' => 'required_if:type,==,employee'
                ]);

                DB::beginTransaction();
                $user = new User();
                $user = $user->fill($request->all());
                $user['password'] = Hash::make($request->password);
                $user->assignRole($request->role_id);
                $user->save();
                DB::commit();

                // $token = $user->createToken('pmsToken')->accessToken;
                return response()->json([
                    'payload' => $user,
                    'status' => true
                ], 201);
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
            if (auth()->user()->can('edit user')) {
                $user = User::find($id);
                $user->fill($request->only('name', 'email', 'phone_number', 'role_id'));
                if ($user->save()) {
                    return response()->json([
                        'success' => true,
                        'payload' => $user
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => "Error in updating"
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
     * change the password in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function change_password(Request $request, $id)
    {
        try {
            if (auth()->user()->can('edit user')) {

                $this->validate($request, [
                    'oldpassowrd' => "required",
                    'password' => "required|same:c_password",
                    'c_password' => "required"
                ]);
                if (Hash::check($request->oldpassword, auth()->user()->password)) {
                    $user =  User::where('id', auth()->user()->id)
                        ->update(['password', Hash::make($request->password)]);
                    return response()->json([
                        'status' => true,
                        'payload' => $user
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => "Wrong password!"
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
            if (auth()->user()->can('delete user')) {
                $user = User::find($id);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'error' => "Not found"
                    ], 404);
                }
                if ($user->delete()) {
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
}
