<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReviewController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| PUBLIC ROOMS (без авторизации)
|--------------------------------------------------------------------------
*/
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::get('/rooms/{id}/schedule', [RoomController::class, 'schedule']);
Route::get('/rooms/{id}/rating', [RoomController::class, 'rating']);


/*
|--------------------------------------------------------------------------
| PROTECTED
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | BOOKINGS (все авторизованные)
    |--------------------------------------------------------------------------
    */
    Route::get('/my-bookings', [BookingController::class, 'my']);

    Route::post('/bookings', [BookingController::class, 'store']);

    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);


    /*
    |--------------------------------------------------------------------------
    | ADMIN + MANAGER
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,manager')->group(function () {

        // управление комнатами
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{id}', [RoomController::class, 'update']);

        // список всех бронирований
        Route::get('/bookings', [BookingController::class, 'index']);

        // подтверждение брони
        Route::patch('/bookings/{id}/confirm', [BookingController::class, 'confirm']);

        // поиск свободных комнат
        Route::get('/rooms/available', [RoomController::class, 'available']);
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        // удаление комнаты
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | REVIEWS (все авторизованные)
    |--------------------------------------------------------------------------
    */
    Route::get('/rooms/{id}/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});