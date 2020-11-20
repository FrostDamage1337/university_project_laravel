<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Placed_Ticket;
use App\Models\Client;
use App\Models\User;
use App\Models\Executor;

class PlacedTicketController extends Controller
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
            'result' => (isset($request->placed_ticket_id)) ? 
                Placed_Ticket::where('id', $request->placed_ticket_id)->first() : 
                Placed_Ticket::get(),
        ]);
    }

    public static function create(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($client = Client::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a client' ];
        }

        if (empty($request->description) || 
            empty($request->country) ||
            empty($request->from) ||
            empty($request->to)
        ) {
            return [ 'code' => 500, 'message' => 'Not all data was filled' ];
        }

        try {
            $ticket = new Placed_Ticket;
            $ticket->client_id = $client->id;
            $ticket->description = $request->description;
            $ticket->country = $request->country;
            $ticket->from = date('Y-m-d H:i:s', strtotime($request->from));
            $ticket->to = date('Y-m-d H:i:s', strtotime($request->to));

            $ticket->save();
        } catch (\Illuminate\Database\QueryException $e) {
            return [ 'code' => 500, 'message' => 'Some data is incorrect' ];
        }
        return [ 'code' => 200, 'message' => 'OK', 'result' => [ 'id' => $ticket->id ] ];
    }
}
