<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;

class ForgotPasswordController extends BaseController
{
    public function forgotPassword(ForgotPasswordRequest $request){
        $input=$request->only('email');
        $user=User::where('email',$input)->first();
        $user->notify(new ResetPasswordNotification());
        return $this->sendResponse('true',__('ApiMessage.success'));
    }
}