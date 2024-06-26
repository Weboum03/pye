<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('password/forgot',[ForgotPasswordController::class,'forgotPassword']);
Route::post('password/reset',[ResetPasswordController::class,'resetPassword']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    $router->post('login', [AuthController::class, 'login']);
    $router->post('register', [AuthController::class, 'register']);
    $router->post('logout', [AuthController::class, 'logout']);
    $router->post('refresh', [AuthController::class, 'refresh']);
    $router->post('forgot-password', [AuthController::class, 'forgotPassword']);
    $router->post('confirm-otp', [AuthController::class, 'confirmOtp']);
    $router->post('reset-password', [AuthController::class, 'passwordReset']);
    $router->post('me', [AuthController::class, 'me']);
    $router->apiResource('users', UserController::class);
});
