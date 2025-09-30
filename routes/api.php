<?php

use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminMemberController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardMemberController;
use App\Http\Controllers\AdminGuestController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserChatController;
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
    Route::post('forgot-password', [ForgotPasswordController::class, 'resetByMember']);
    
});

// ====== MEMBER ROUTES ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'auth',
], function () {
    Route::get('/dashboard', [UserDashboardController::class, 'getDashboard']);
    Route::get('/transaction', [UserDashboardController::class, 'getAllTransactions']);
    Route::get('/barcode', [UserDashboardController::class, 'barcodeMember']);
    Route::post('/change-password', [ForgotPasswordController::class, 'changePassword']);
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
    Route::get('/profile-member', [MemberProfileController::class, 'show']);
    Route::put('/profile-update', [MemberProfileController::class, 'update']);
    Route::post('/profile-photo', [MemberProfileController::class, 'uploadPhoto']);
    Route::post('/profile-photo/delete', [MemberProfileController::class, 'deletePhoto']);
});

// ====== ADMIN ROUTES ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'admin',
], function () {

    Route::post('/register-cs', [AuthController::class, 'registerCs']);

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

    Route::get('/card-admin', [AdminController::class, 'index']);

    Route::get('/promo', [PromoController::class, 'index']);
    Route::post('/promo-save', [PromoController::class, 'store']);
    Route::delete('/promo/{id}', [PromoController::class, 'destroy']);

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

// ====== USER CHAT ROUTES ======
Route::group([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'message',
], function () {
    Route::get('/chats', [UserChatController::class, 'getMessages']);
    Route::get('/chats-admin', [UserChatController::class, 'getAdminMessages']);
    Route::post('/chats-send', [UserChatController::class, 'sendMessage']);

    Route::get('/admin-user', [AdminChatController::class, 'getUsers']);
    Route::get('/chats/{userId}', [AdminChatController::class, 'getMessages']);
    Route::post('/chats/{userId}', [AdminChatController::class, 'sendMessage']);


});