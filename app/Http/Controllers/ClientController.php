<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;

class ClientController extends Controller
{
    public static function editInfo(Request $request) {
        $info = json_decode($request->info);
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        try {
            Client::where('id', $user->id)->update([
                'name' => $request->name,
                'country' => $request->country,
                'photo_path' => $request->photo_path,
                'description' => $request->description
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return json_encode([
                "code" => 500,
                "message" => "Unsupported formats",
            ]);
        }

        return json_encode([
            "code" => 200,
            "message" => "OK",
        ]);
    }

    public static function getInfo(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        } else {
            if (!($client = Client::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not a client' ];
            }
            return json_encode([
                'code' => 200,
                'message' => 'OK',
                'result' => [
                    'name' => $client->name,
                    'country' => $client->country,
                    'photo_path' => $client->photo_path,
                    'description' => $client->description,
                ],
            ]);
        }
    }
}
