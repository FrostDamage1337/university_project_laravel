<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;

class Auth extends Controller
{
    public function auth(Request $request) {
        $login = $request->input('login');
        $password = $request->input('password');
        $is_mobile = ($request->is_mobile) ? $request->is_mobile : false;
        $token = UserController::login($login, $password, $is_mobile);
        return $token;
    }

    public function register(Request $request) {
        $login = $request->input('login');
        $password = $request->input('password');
        $is_client = $request->input('is_client');

        $user = UserController::register($login, $password, $is_client);
        
        if (!$user) {
            return [ 'code' => 500, 'message' => 'Login already registered' ];
        }

        return $user;
    }
}
