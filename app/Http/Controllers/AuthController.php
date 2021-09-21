<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    //
    public function register(Request $req)
    {
        $this->validate($req, [
            'name' => "required|min:3",
            'email' => "required|email",
            'password' => "required|min:5",
            'role_id' => 'required'
        ]);

        DB::beginTransaction();
        $user = new User();
        $user = $user->fill($req->all());
        $user['password'] = bcrypt($req->password);
        $user->assignRole($req->role_id);
        $user->save();
        DB::commit();

        $token = $user->createToken('pmsToken')->accessToken;
        return response()->json([
            $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $req)
    {


        if (Auth::guard('web')->attempt($req->only('email', 'password'))) {
            $user = User::where('email', $req->email)->first();
            $token = $user->createToken('pmsToken')->accessToken;
            return response()->json([
                'token' => $token,
                'msg' => "you have successfully logged in!",

            ], 200);
        } else {
            return response()->json([
                'error' => "Unauthorized!"
            ], 401);
        }
    }

    public function logout(Request $req)
    {
        // Auth::guard('api')->logout();
        // if(Auth::logout()){
        // dd(auth()->user());

        // echo auth()->user();
        $accessToken = Auth::user()->token();
        //  dd($accessToken);
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();
        //  echo auth()->user();
        return response()->json(null, 204);
    }
}
