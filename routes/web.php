<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;

Route::get('/', [UserController::class, 'index']);
Route::get('/call', [UserController::class, 'call']);

// User routes for web interface
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/create', [UserController::class, 'create']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/{id}/edit', [UserController::class, 'edit']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Ticket routes for web interface
Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/create', [TicketController::class, 'create']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::get('/tickets/{id}', [TicketController::class, 'show']);
Route::get('/tickets/{id}/edit', [TicketController::class, 'edit']);
Route::put('/tickets/{id}', [TicketController::class, 'update']);
