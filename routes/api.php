<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ShiftController;
use App\Http\Controllers\TransactionController;
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
Route::post('generate-token', [UserController::class, 'generateToken']);
Route::get('fetch-verification-session', [UserController::class, 'fetchVerificationSession']);

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

Route::group([
    'middleware' => 'auth:api'
], function ($router) {
    $router->get('gateway/access_token', [ShiftController::class, 'accessToken']);
    $router->get('gateway/invoice/{invoiceId}', [ShiftController::class, 'invoice']);
    $router->get('gateway/invoice', [ShiftController::class, 'invoice']);
    $router->post('gateway/refund', [ShiftController::class, 'refund']);
    $router->post('gateway/sale', [ShiftController::class, 'sale']);
    $router->post('gateway/tokenAdd', [ShiftController::class, 'tokenAdd']);
    $router->get('gateway/void', [ShiftController::class, 'void']);
});

Route::middleware('api.key')->group(function ($router) {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });

    $router->get('orders/{id}', [OrderController::class, 'show']);
    $router->post('orders', [OrderController::class, 'create']);
    $router->post('orders/{id}/process-payment', [OrderController::class, 'processPayment']);
    $router->post('transactions/refund', [OrderController::class, 'refund']);
});