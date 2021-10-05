<?php

namespace App\Http\Controllers;


use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ReusableTrait;
use App\Http\Traits\ResponseTrait;

class UserController extends Controller
{
    use ReusableTrait, ResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // private $reusable = ReusableQueries::class;
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
                //retrieve child roles
                $roles = $this->get_child_roles(auth()->user());
                $users = User::whereIn('role_id', $roles)->get();
                if ($users) {
                    return $this->ok_response(true, $users, 200);
                } else {
                    return $this->error_response(false, "No user exist!", 404);
                }
            } else {
                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
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
                return $this->ok_response(true, $user, 201);
            } else {

                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
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
            if (auth()->user()->can('retrieve user')) {
                $user = User::find($id);
                if ($user) {
                    return $this->ok_response(true, $user, 200);
                }
                return $this->error_response(false, "No such user exist!", 404);
            } else {
                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
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
            if (auth()->user()->can('edit user')) {
                $user = User::find($id);
                $user->fill($request->only('name', 'email', 'phone_number', 'role_id'));
                if ($user->save()) {
                    return $this->ok_response(true, $user, 201);
                } else {
                    return $this->error_response(false, "Error in updating", 400);
                }
            } else {
                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
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
                    return $this->ok_response(true, $user, 201);
                } else {
                    return $this->error_response(false, "Wrong password!", 400);
                }
            } else {
                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
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
                    return $this->error_response(false, "Not found", 404);
                }
                if ($user->delete()) {
                    return $this->ok_response(true, [], 200);
                } else {
                    return $this->error_response(false, "Error in deleting", 400);
                }
            } else {
                return $this->error_response(false, "Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response(false, $e->getMessage(), 500);
        }
    }
}
