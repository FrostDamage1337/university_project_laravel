<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Executor;

class UserController extends Controller
{
    public static function login($login, $password, $is_mobile = false) {
        $user = User::where([ ['login', $login ], [ 'password', $password ]])->first();
        if (!$user) {
            return false;
        }

        if ($is_mobile) {
            $token = User::where('id', $user->id)->first()->token;
        } else {
            $token = bin2hex(random_bytes(10));
            
            User::where('id', $user->id)->update(['token' => $token ]);
        }

        return $token;
    }

    public static function register($login, $password, $is_client) {
        if (empty($login) || empty($password) || empty($is_client)) {
            return false;
        }

        $token = bin2hex(random_bytes(10));
        $user = new User;
        $user->login = $login;
        $user->password = $password;
        $user->token = $token;
        $user->money = 0;
        $user->is_client = ($is_client == 'true') ? 1 : 0;

        try {
            $user->save();
        } catch (\Illuminate\Database\QueryException $e) {
            return false;
        }

        if ($is_client == 'true') {
            $client = new Client;
            $client->id = $user->id;
            $client->save();
        } else {
            $executor = new Executor;
            $executor->id = $user->id;
            $executor->save();
        }

        return $user;
    }

    public static function topUpWallet(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            if (!($client = Client::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not registered' ];
            }
        }

        $amount = $request->amount;

        $callback = true; // here will be a Payment with foreign API

        if ($callback) {
            User::where('id', $user->id)->update([ 'money' => DB::raw('money + '.$amount) ]);
        }
        
        return json_encode([
            'code' => 200,
            'message' => 'OK'
        ]);
    }
}
