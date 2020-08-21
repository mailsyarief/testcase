<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\User;
use App\Providers\ResponseProvider;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        $data = request(['name', 'email', 'password']);
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return ResponseProvider::http(true, "Register Success", NULL, 200);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return ResponseProvider::http(false, "Unauthorized", NULL, 401);
        }

        return ResponseProvider::http(true, "Login Success", ["token" => $token], 200);
    }


    public function user()
    {
        return ResponseProvider::http(true, "User Data", auth()->user(), 200);
    }

    public function logout()
    {
        auth()->logout();
        return ResponseProvider::http(true, "Logout Success", NULL, 200);
    }
}
