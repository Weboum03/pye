<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    private $otp;
    public function __construct(){
        $this->otp=new Otp();
    }
    public function resetPassword(ResetPasswordRequest $request){
        $otp2=$this->otp->validate($request->email,$request->otp);
        if(! $otp2->status){
            return response()->json(['error'=>$otp2],401);

        }
        $user=User::where('email',$request->email)->first();
        $user->update(
            [
                'password'=>Hash::make($request->password)
            ]
        );
       $user->tokens()->delete();
        $success['succees']=true;
        return response()->json($success,200);
    }
}