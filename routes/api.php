<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminMemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardMemberController;
use App\Http\Controllers\AdminGuestController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ====== AUTH ROUTES ======
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('check-membership', [AuthController::class, 'checkMembership']);
});

// ====== MEMBER ROUTES ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'auth',
], function () {
    Route::get('/dashboard', [UserDashboardController::class, 'getDashboard']);
    Route::get('/transaction', [UserDashboardController::class, 'getAllTransactions']);
});


// ====== EMAIL VERIFICATION ======
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // otomatis markEmailAsVerified

    return response()->json(['message' => 'Email berhasil diverifikasi.']);
})->middleware(['auth:api', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email sudah diverifikasi']);
    }
    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Link verifikasi baru telah dikirim']);
})->middleware(['auth:api']);

Route::middleware(['auth:api', 'verified'])->get('/profile', function (Request $request) {
    return $request->user();
});

// ====== MEMBER MANAGEMENT ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'member',
], function () {
    Route::get('/profile', [CardMemberController::class, 'profile']);
    Route::post('/register-profil', [CardMemberController::class, 'registerOrUpdate']);
    Route::patch('/status', [CardMemberController::class, 'updateStatus']);
    Route::delete('/delete', [CardMemberController::class, 'delete']);
});

// ====== ADMIN ROUTES ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'admin',
], function () {

    Route::get('/card-members', [AdminMemberController::class, 'index']);
    Route::get('/card-members/{id}', [AdminMemberController::class, 'show']);
    Route::post('/card-members/{id}/activation', [AdminMemberController::class, 'updateActivation']);
    Route::post('/card-members/{id}/validation', [AdminMemberController::class, 'updateValidation']);

    Route::get('/transaksi', [TransaksiController::class, 'index']);
    Route::get('/transaksi/{transNo}', [TransaksiController::class, 'show']);

    Route::get('/dashboard', [AdminDashboardController::class, 'getDashboardSummary']);
    Route::get('/card-guest', [AdminGuestController::class, 'index']);
    Route::get('/card-guest/{id}', [AdminGuestController::class, 'show']);
    Route::post('/card-guest/{id}/activate', [AdminGuestController::class, 'activate']);
    Route::post('/card-guest/{id}/update-card', [AdminGuestController::class, 'updateCardNo']);
    Route::post('/activated-callback', [AdminGuestController::class, 'activatedCallback']);

});


// ====== GUEST ROUTES ======

Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'guest',
], function () {

    Route::get('/create', [GuestController::class, 'create'])->name('guest.create'); 
    Route::post('/send', [GuestController::class, 'store'])->name('guest.store'); 
    Route::get('/waiting-approval',[GuestController::class, 'waitingApproval'])->name('waitingApproval');
    Route::get('/me',[AdminGuestController::class, 'me'])->name('me');
});