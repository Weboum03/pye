<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\TransactionController;

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
    return redirect('admin/login');
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('admin.register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('admin.password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('admin.password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('admin.password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('admin.password.store');
});

Route::middleware('auth:admin')->group(function () {
    
    Route::get('dashboard', function () {
        return view('admins.dashboard');
    })->name('admin.dashboard');

    Route::get('verify-email', EmailVerificationPromptController::class)->name('admin.verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('admin.verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('admin.verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('admin.password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('admin.password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('admin.profile.destroy');

    Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('users', [UserController::class, 'edit'])->name('admin.users');
    Route::get('users/{id}', [UserController::class, 'show'])->name('admin.users.show');
    Route::patch('users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::post('merchants', [MerchantController::class, 'store'])->name('admin.merchants.store');
    Route::get('merchants', [MerchantController::class, 'edit'])->name('admin.merchants');
    Route::get('merchants/{id}', [MerchantController::class, 'show'])->name('admin.merchants.show');
    Route::patch('merchants/{id}', [MerchantController::class, 'update'])->name('admin.merchants.update');
    Route::delete('merchants/{id}', [MerchantController::class, 'destroy'])->name('admin.merchants.destroy');

    Route::post('transactions', [TransactionController::class, 'store'])->name('admin.transactions.store');
    Route::get('transactions', [TransactionController::class, 'edit'])->name('admin.transactions');
    Route::get('transactions/{id}', [TransactionController::class, 'show'])->name('admin.transactions.show');
    Route::patch('transactions/{id}', [TransactionController::class, 'update'])->name('admin.transactions.update');
    Route::delete('transactions/{id}', [TransactionController::class, 'destroy'])->name('admin.transactions.destroy');

    Route::post('admins', [AdminController::class, 'store'])->name('admin.admins.store');
    Route::get('admins', [AdminController::class, 'edit'])->name('admin.admins');
    Route::get('admins/{id}', [AdminController::class, 'show'])->name('admin.admins.show');
    Route::patch('admins/{id}', [AdminController::class, 'update'])->name('admin.admins.update');
    Route::delete('admins/{id}', [AdminController::class, 'destroy'])->name('admin.admins.destroy');
});
