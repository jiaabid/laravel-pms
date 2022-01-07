<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ResponseTrait;
use Exception;

class AuthController extends Controller
{
    use  ResponseTrait;

    /**
     * login
     *
     * @param  mixed $req
     * @return \Illuminate\Http\Response
     */
    public function login(Request $req)
    {

        // if (Auth::guard('web')->attempt($req->only('email', 'password'))) {
          
            $user = User::where('email', $req->email)->with('detail')->with('role:id,name')->with('department:id,name')->first();
            // dd(Hash::check($req->password, $user->password));
            // return Hash::check($req->password, $user->password);
            if (!$user) {
                return $this->error_response("Invalid Email", 400);
            } 
            if (!Hash::check($req->password, $user->password)) {
                return $this->error_response("Invalid Password", 400);
            }
            $token = $user->createToken('pmsToken')->accessToken;
            
            if ($token) {
                return $this->success_response([
                    'token' => $token,
                    'msg' => "you have successfully logged in!",
                    'user' => $user

                ], 200);
            }
        // } else {
        //     return $this->error_response("Forbidden!", 403);
        // }
    }

    /**
     * logout
     *
     * @param  mixed $req
     *@return \Illuminate\Http\Response
     */
    public function logout(Request $req)
    {

        $accessToken = Auth::user()->token();
        // DB::table('oauth_refresh_tokens')
        //     ->where('access_token_id', $accessToken->id)
        //     ->update([
        //         'revoked' => true
        //     ]);

        $accessToken->revoke();
        //  echo auth()->user();
        return $this->success_response(null, 204);
    }
}


//moved to UserContrller 

    // public function register(Request $req)
    // {
    //     $this->validate($req, [
    //         'name' => "required|min:3",
    //         'email' => "required|email",
    //         'password' => "required|min:5",
    //         'role_id' => 'required'
    //     ]);

    //     DB::beginTransaction();
    //     $user = new User();
    //     $user = $user->fill($req->all());
    //     $user['password'] = bcrypt($req->password);
    //     $user->assignRole($req->role_id);
    //     $user->save();
    //     DB::commit();

    //     $token = $user->createToken('pmsToken')->accessToken;
    //     return response()->json([
    //         $user,
    //         'token' => $token
    //     ], 201);
    // }