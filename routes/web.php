<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:admin'])->name('dashboard');

Route::middleware('auth:admin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
});

require __DIR__.'/auth.php';
