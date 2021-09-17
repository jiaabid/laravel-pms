<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function register(Request $req)
    {
        $this->validate($req, [
            'name' => "required|min:3",
            'email' => "required|email",
            'password' => "required|min:5"
        ]);

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => bcrypt($req->password)
        ]);
        $token = $user->createToken('pmsToken')->accessToken;
        return response()->json([
            $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $req)
    {

        if (Auth::attempt($req->only('email', 'password'))) {
            $token = auth()->user()->createToken('pmsToken')->accessToken;
            return response()->json([
                'token' => $token,
                'msg' => "you have successfully logged in!"
            ], 200);
        } else {
            return response()->json([
                'error' => "Unauthorized!"
            ], 401);
        }
    }

    public function logout(Request $req)
    {
        Auth::logout();
    }
}
