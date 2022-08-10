<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Authentication guest routes
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Authenticated Routes
Route::group(['middleware' => 'auth:sanctum'], function() {

    // Authenticated user actions - related to auth
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Authenticated user actions - related to loan
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.create');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('/loans/{loan}/pay', [LoanController::class, 'pay'])->name('loans.pay');

    Route::get('/test', function() {
        return ['user' => auth()->user(), 'xx' => auth()->user()->isAdmin()];
    });

    // Authenticated admin action - related to loan
    Route::post(
        '/loans/{loan}/approve',
        [AdminController::class, 'approve']
    )->middleware('can:beAdmin')->name('loans.approve');
});