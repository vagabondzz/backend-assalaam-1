<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello Laravel';
});
Route::get('register', function () {
    return view('auth.register');
});
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/dashboard', function () {
    return view('dashboard'); // nanti buat dashboard.blade.php
})->middleware('auth');
