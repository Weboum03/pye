<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\IpWhitelistController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth:merchant')->group(function () {

    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/company', [ProfileController::class, 'updateCompany'])->name('profile.update.company');
    Route::patch('profile/key', [ProfileController::class, 'updateKey'])->name('profile.update.key');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users', [UserController::class, 'edit'])->name('users');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
    Route::get('admins', [AdminController::class, 'edit'])->name('admins');
    Route::get('admins/{id}', [AdminController::class, 'show'])->name('admins.show');
    Route::patch('admins/{id}', [AdminController::class, 'update'])->name('admins.update');
    Route::delete('admins/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');

    Route::post('merchants', [MerchantController::class, 'store'])->name('merchants.store');
    Route::get('merchants', [MerchantController::class, 'edit'])->name('merchants');
    Route::get('merchants/{id}', [MerchantController::class, 'show'])->name('merchants.show');
    Route::patch('merchants/{id}', [MerchantController::class, 'update'])->name('merchants.update');
    Route::delete('merchants/{id}', [MerchantController::class, 'destroy'])->name('merchants.destroy');

    Route::get('transactions', [TransactionController::class, 'edit'])->name('transactions');
    Route::get('transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::patch('transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::patch('transactions/{id}/refund', [TransactionController::class, 'refund'])->name('transactions.refund');

    Route::get('orders', [OrderController::class, 'edit'])->name('orders');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{id}', [OrderController::class, 'update'])->name('orders.update');
    

    Route::post('ip_whitelists', [IpWhitelistController::class, 'store'])->name('ip_whitelists.store');
    Route::get('ip_whitelists', [IpWhitelistController::class, 'edit'])->name('ip_whitelists');
    Route::get('ip_whitelists/{id}', [IpWhitelistController::class, 'show'])->name('ip_whitelists.show');
    Route::patch('ip_whitelists/{id}', [IpWhitelistController::class, 'update'])->name('ip_whitelists.update');
    Route::delete('ip_whitelists/{id}', [IpWhitelistController::class, 'destroy'])->name('ip_whitelists.destroy');

    Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('companies', [CompanyController::class, 'edit'])->name('companies');
    Route::get('companies/{id}', [CompanyController::class, 'show'])->name('companies.show');
    Route::patch('companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('keys');;
    Route::post('api-keys', [ApiKeyController::class, 'generate'])->name('key.generate');
    Route::delete('api-keys/{id}', [ApiKeyController::class, 'revoke'])->name('key.revoke');

    Route::middleware('api.key')->group(function () {
        Route::get('/protected-route', function () {
            return response()->json(['message' => 'This is a protected route']);
        });
    });
});


