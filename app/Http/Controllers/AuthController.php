<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Providers\ResponseProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->middleware('jwt', ['except' => ['login', 'register']]);
        $this->user = $user;
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'name' => 'required|string',
            'password' => 'required'
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

        $this->user->create(
            $request->input('name'),
            $request->input('email'),
            $request->input('password'),
        );

        return ResponseProvider::http(true, "Register Success", NULL, 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email ',
            'password' => 'required'
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

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

    public function users()
    {
        $users = $this->user->findAll();
        return ResponseProvider::http(true, "User Data", $users, 200);
    }

    public function userDetail($user_id)
    {
        $user = $this->user->findById($user_id);
        if(!$user) return ResponseProvider::http(false, "User Not Found", null, 404);
        return ResponseProvider::http(true, "User Data", $user, 200);
    }

    public function logout()
    {
        auth()->logout();
        return ResponseProvider::http(true, "Logout Success", NULL, 200);
    }
}
