<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Ready_Ticket;
use App\Models\Executors_On_Ticket;
use App\Models\Placed_Ticket;
use App\Models\Executor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\TicketHistoryController;

class ReadyTicketController extends Controller
{
    public static function getMachineUrl() {
        return 'https://example.com/1/'; // for testing
    }

    public static function get(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            if (!($client = Client::where('id', $user->id)->first())) {
                return [ 'code' => 500, 'message' => 'This user is not defined' ];
            }
        }

        return json_encode([
            'code' => 200,
            'message' => 'OK',
            'result' => isset($request->ready_ticket_id) ?
                Ready_Ticket::where('id', $request->ready_ticket_id)->first() :
                Ready_Ticket::get(),
        ]);
    }

    public static function chooseExecutor(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($client = Client::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a client' ];
        }

        if (empty($request->chosen_id)) {
            return [ 'code' => 500, 'message' => 'Not all data was filled' ];
        }

        $chosen_ticket = Executors_On_Ticket::where('id', $request->chosen_id)->first();

        if (Placed_Ticket::where('id', $chosen_ticket->placed_ticket_id)->first()->chosen) {
            return [ 'code' => 500, 'message' => "Executor is already chosen" ];
        }

        $price = $chosen_ticket->price;
        $wallet = User::where('id', $user->id)->first()->money;
        if ($wallet - $price < 0) {
            return [ 'code' => 500, 'message' => "You don't have enough money for transaction. Please top up your wallet" ];
        }

        User::where('id', $user->id)->update([ 'money' => DB::raw('money - '.$price)]);
        User::where('id', $chosen_ticket->executor_id)->update([ 'money' => DB::raw('money + '.$price)]);

        try {
            $ready_ticket = new Ready_Ticket;
            $ready_ticket->client_id = $client->id;
            $ready_ticket->executor_id = $chosen_ticket->executor_id;
            $ready_ticket->placed_ticket_id = $chosen_ticket->placed_ticket_id;
            $ready_ticket->chosen_id = $request->chosen_id;
            $ready_ticket->active = 0;
            $ready_ticket->video_path = '';
            $ready_ticket->url = self::getMachineUrl();

            $ready_ticket->save();
        } catch (Exception $e) {
            return [ 'code' => 500, 'message' => 'Some data is incorrect' ];
        }
        Placed_Ticket::where('id', $chosen_ticket->placed_ticket_id)->update([ 'chosen' => 1 ]);

        return [ 'code' => 200, 'message' => 'OK', 'result' => [ 'id' => $ready_ticket->id ] ];

    }
    
    public static function participate(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Executor::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a executor' ];
        }

        $ticket_id = $request->ready_ticket_id;

        $user_on_tickets = DB::table('ready__tickets')
            ->select('executors__on__tickets.executor_id as id')
            ->join('executors__on__tickets', 'ready__tickets.executor_id', '=', 'executors__on__tickets.executor_id')
            ->where('executors__on__tickets.id', $user->id)
            ->where('ready__tickets.active', '1')
            ->get();

        if (count($user_on_tickets) > 0) {
            return [ 'code' => 500, 'message' => 'User is already handling some ticket' ];
        }

        if (!($ready_ticket = Ready_Ticket::where('id', $ticket_id)->first())) {
            return [ 'code' => 500, 'message' => 'Ticket is undefined' ];
        }

        $placed_ticket = Placed_Ticket::where('id', $ready_ticket->placed_ticket_id)->first();
        $time_start = $placed_ticket->from;

        if (strtotime($time_start) > strtotime(date(NOW()))) {
            return [ 'code' => 500, 'message' => "It's too early to participate" ];
        }
        $url = $ready_ticket->url;

        /*$video_path = Http::post("{$url}iot/getVideo", [
            'token' => $token,
            'ready_ticket_id' => $ticket_id,
        ]);*/

        $video_path = '/somewhere/path/in/here'; // for testing

        if (!$video_path) {
            return [ 'code' => 500, 'message' => "No connection to IoT machine" ];
        }

        Ready_Ticket::where('id', $ticket_id)->update(['video_path' => $video_path, 'active' => 1 ]);

        return json_encode([
            'code' => 200,
            'message' => 'IoT machine ready to receive inputs',
            'result' => [
                'url' => $url,
            ]
        ]);
    }

    public static function handleIoTMovements(Request $request) {
        $axis_x = $request->x;
        $axis_y = $request->y;

        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Client::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a executor' ];
        }

        $url = $request->url;

        if (empty($axis_x) ||
            empty($axis_y) ||
            empty($url)
        ) {
            return [ 'code' => 500, 'message' => 'Some data is empty' ];
        }

        if (Http::post('{$url}iot/moveIot', [
            'axis_x' => $axis_x,
            'axis_y' => $axis_y,
            'token' => $token,
        ])->failed()) {
            Http::post('{$url}iot/criticalStop', [
                'token' => $token
            ]);

            return [ 'code' => 500, 'message' => 'Data is corrupted. Critical stopping a IoT machine' ];
        }

        return [ 'code' => 200, 'message' => 'OK' ];
    }

    public static function endParticipating(Request $request) {
        $token = $request->token;

        if (!($user = User::where('token', $token)->first())) {
            return [ 'code' => 403, 'message' => 'Please authorize' ];
        }

        if (!($executor = Client::where('id', $user->id)->first())) {
            return [ 'code' => 500, 'message' => 'This user is not a executor' ];
        }

        $ready_ticket_id = $request->ready_ticket_id;

        $ready_ticket = Ready_Ticket::where('id', $ready_ticket_id)->first();
        Ready_Ticket::where('id', $ready_ticket_id)->delete();
        TicketHistoryController::createRecord($ready_ticket);
    }
}
