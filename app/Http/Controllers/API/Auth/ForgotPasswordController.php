<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request){
        $input=$request->only('email');
        $user=User::where('email',$input)->first();
        $user->notify(new ResetPasswordNotification());
        $success['succees']=true;
        return response()->json($success,200);
    }
}