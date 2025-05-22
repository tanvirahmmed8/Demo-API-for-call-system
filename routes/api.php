<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User routes
Route::get('/user/{id}', [UserController::class, 'findUserById']);
Route::get('/user-phone/{phonenumber}', [UserController::class, 'findUserByPhoneNumber']);
Route::post('/user/create', [UserController::class, 'createUser']);

// Ticket routes
Route::post('/user/ticket-create', [TicketController::class, 'createUserTicket']);
Route::post('/user/ticket-create/phn', [TicketController::class, 'createUserTicketPhn']);
Route::get('/user/ticket/{id}', [TicketController::class, 'findUserTicket']);
Route::get('/user/last-ticket/{id}', [TicketController::class, 'findLastIssuedTicket']);
Route::put('/ticket-status/{id}', [TicketController::class, 'updateTicketStatus']);
