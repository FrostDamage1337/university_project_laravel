<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ExecutorController;
use App\Http\Controllers\PlacedTicketController;
use App\Http\Controllers\ReadyTicketController;
use App\Http\Controllers\ExecutorsOnTicketController;
use App\Http\Controllers\TicketHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocalisationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('user')->group(function() {
    Route::prefix('wallet')->group(function() {
        Route::post('/topUpWallet', [ UserController::class, 'topUpWallet' ]);
    });
    Route::prefix('auth')->group(function() {
        Route::post('/', [ Auth::class, 'auth' ]);
        Route::post('/register', [ Auth::class, 'register' ]);
    });
});

Route::prefix('client')->group(function() {
    Route::post('/editInfo', [ ClientController::class, 'editInfo' ]);
    Route::post('/getInfo', [ ClientController::class, 'getInfo' ]);
    Route::prefix('ticket')->group(function() {
        Route::post('/create', [ PlacedTicketController::class, 'create' ]);
        Route::post('/getExecutors', [ ExecutorsOnTicketController::class, 'get' ]);
        Route::post('/chooseExecutor', [ ReadyTicketController::class, 'chooseExecutor' ]);
        Route::post('/get', [ ReadyTicketController::class, 'get' ]);
        Route::post('/leaveReport', [ TicketHistoryController::class, 'leaveReport' ]);
        Route::post('/getHistory', [ TicketHistoryController::class, 'getHistory' ]);
    });
});

Route::prefix('executor')->group(function() {
    Route::post('/editInfo', [ ExecutorController::class, 'editInfo' ]);
    Route::post('/getInfo', [ ExecutorController::class, 'getInfo ']);
    Route::prefix('ticket')->group(function() {
        Route::post('/respond', [ ExecutorsOnTicketController::class, 'respond' ]);
        Route::post('/participate', [ ReadyTicketController::class, 'participate' ]);
        Route::post('/getExecutors', [ ExecutorsOnTicketController::class, 'get' ]);
        Route::post('/get', [ ReadyTicketController::class, 'get' ]);
        Route::post('/handleIoTMovements', [ ReadyTicketController::class, 'handleIoTMovements' ]);
        Route::post('/endParticipating', [ ReadyTicketController::class, 'endParticipating' ]);
        Route::post('/getHistory', [ TicketHistoryController::class, 'getHistory' ]);
    });
});

Route::post('/localisation/get', [ LocalisationController::class, 'get' ]);