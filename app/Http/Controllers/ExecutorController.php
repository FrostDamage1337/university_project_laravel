<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Executor;
use App\Models\User;

class ExecutorController extends Controller
{
    public static function editInfo(Request $request) {
        $info = json_decode($request->info);
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        try {
            Executor::where('id', $user->id)->update([
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
            if (!($executor = Executor::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not a client' ];
            }
            return json_encode([
                'code' => 200,
                'message' => 'OK',
                'result' => [
                    'name' => $executor->name,
                    'country' => $executor->country,
                    'photo_path' => $executor->photo_path,
                    'description' => $executor->description,
                    'rating' => $executor->rating,
                ],
            ]);
        }
    }
}
