<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Executor;
use App\Models\Executors_On_Ticket;
use App\Models\Client;
use App\Models\Placed_Ticket;
use Illuminate\Support\Facades\DB;

class ExecutorsOnTicketController extends Controller
{
    public static function get(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            if (!($client = Client::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not a executor' ];
            }
        }

        return json_encode([
            'code' => 200,
            'message' => 'OK',
            'result' => isset($request->executors_on_ticket_id) ? Executors_On_Ticket::where('id', $request->executors_on_ticket_id)->first() : Executors_On_Ticket::get(),
        ]);
    }

    public static function respond(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not an executor' ];
        }

        if (empty($request->price) ||
            empty($request->description) ||
            empty($request->placed_ticket_id)
        ) {
            return [ 'code' => 500, 'message' => 'Not all data was filled' ];
        }

        try {
            $executors = new Executors_On_Ticket;

            $executors->placed_ticket_id = $request->placed_ticket_id;
            $executors->executor_id = $executor->id;
            $executors->price = $request->price;
            $executors->description = $request->description;
            
            $executors->save();
        } catch (\Illuminate\Database\QueryException $e) {
            return [ 'code' => 500, 'message' => 'Some data is incorrect' ];
        }

        return [ 'code' => 200, 'message' => 'OK', 'result' => [ 'id' => $executors->id ] ];
    }
}
