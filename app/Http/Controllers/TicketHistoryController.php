<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket_History;
use App\Models\Client;
use App\Models\User;
use App\Models\Executor;

class TicketHistoryController extends Controller
{
    public static function createRecord($ready_ticket) {
        $ticket_history = new Ticket_History;
        $ticket_history->placed_ticket_id = $ready_ticket->placed_ticket_id;
        $ticket_history->client_id = $ready_ticket->client_id;
        $ticket_history->executor_id = $ready_ticket->executor_id;
        $ticket_history->rating = 0;
        $ticket_history->description = '';
        
        $ticket_history->save();
    }

    public static function leaveReport(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($client = Client::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a client' ];
        }

        $ticket_history_id = $request->ticket_history_id;

        if (Ticket_History::where('id', $ticket_history_id)->first()->client_id != $client->id) {
            return [ 'code' => 500, 'message' => "You weren't client of this ticket" ];
        }

        if (empty($request->rating)) {
            return [ 'code' => 500, 'message' => 'No rating provided' ];
        }

        Ticket_History::where('id', $ticket_history_id)->update([ 'rating' => $request->rating, 'description' => $request->description ]);

        return json_encode([
            'code' => 200,
            'message' => 'OK'
        ]);
    }

    public static function getHistory(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            if (!($client = Client::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not defined' ];
            }
        }

        if (empty($request->client_id) &&
            empty($request->executor_id)
        ) {
            return [ 'code' => 500, 'message' => 'No data is filled' ];
        }

        if (isset($request->client_id)) {
            $result = Ticket_History::where('client_id', $request->client_id)->get();
        } else {
            $result = Ticket_History::where('executor_id', $request->executor_id)->get();
        }

        return json_encode([
            'code' => 200,
            'message' => 'OK',
            'result' => $result,
        ]);
    }
}
