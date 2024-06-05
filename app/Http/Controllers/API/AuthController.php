<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Incorrect Credential'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email'    => 'unique:users|required',
            'phone'    => 'unique:users|required',
            'password' => 'required',
        ];
    
        $input     = $request->only('name', 'email', 'phone', 'dob','password');
        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $user = User::create($input);

        $role = Role::where(['name' => 'Admin'])->first();
        if($role) {
            $user->assignRole([$role->id]);
            $user->role_id = $role->id;
            $user->save();
        }

        return $this->sendSuccess('Success');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $input = $request->only('phone');
        $checkNumber = $this->sanitizePhoneNumber($input['phone']);
        if ($checkNumber['text'] == 'Invalid') {
            return $this->sendError(__('ApiMessage.invalidPhone'), '', 422);
        }
        $input['phone'] = $checkNumber['phoneNumber'];
        $rule = ['phone' => "required|exists:users,phone"];
        $message = ['phone.exists' =>  __('ApiMessage.phoneNotExist')];
        $validator = Validator::make($input, $rule, $message);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
        $user = User::where('phone', $input['phone'])->exists();
        if (!$user) {
            return $this->sendError('Unauthorised', ['error' => 'Not valid.']);
        }
        return $this->sendResponse($user, __('ApiMessage.forgotPassword'));
    }

    public function confirmOtp(Request $request): JsonResponse
    {
        $input = $request->only('otp', 'phone');
        $rules = [
            'phone' => 'required',
            'otp' => 'required',
        ];
    
        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }
        $checkNumber = $this->sanitizePhoneNumber($input['phone']);
        if ($checkNumber['text'] == 'Invalid') {
            return $this->sendError(__('ApiMessage.invalidPhone'), '', 422);
        }
        $input['phone'] = $checkNumber['phoneNumber'];
        $user = User::where('phone', $input['phone'])->exists();

        if (!$user) {
            return $this->sendError('Unauthorised', ['error' => 'Not valid.']);
        }
        if ($input['otp'] != '12345') {
            return $this->sendError(__('ApiMessage.invalidOtp'));
        }
        return $this->sendResponseStatus(__('ApiMessage.confirmOtp'));
    }

    public function passwordReset(Request $request): JsonResponse
    {
        $input = $request->all();
        $rules = [
            'phone' => 'required',
            'password' => 'required',
        ];
    
        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }
        $checkNumber = $this->sanitizePhoneNumber($input['phone']);
        if ($checkNumber['text'] == 'Invalid') {
            return $this->sendError(__('ApiMessage.invalidPhone'), '', 422);
        }
        $input['phone'] = $checkNumber['phoneNumber'];
        $user = User::where('phone', $input['phone'])->exists();

        if (!$user) {
            return $this->sendError('Unauthorised', ['error' => 'Not valid.']);
        }
        $user->update(['password' => Hash::make($input['password'])]);
        return $this->sendResponseStatus(__('ApiMessage.passwordReset'));
    }

    public static function sanitizePhoneNumber($fetchPhoneNumber){
        $RgexCode =  "/^(?:[0-9] ?){8,15}[0-9]$/";
        $fetchPhoneNumber = preg_replace('/[^0-9]/', '', trim($fetchPhoneNumber)); // Make only integer
        $fetchPhoneNumber = ltrim($fetchPhoneNumber, '0'); // remove zero's
         if (preg_match($RgexCode, $fetchPhoneNumber)) {
             $statusText = "Valid";
         } else {
             $statusText = "Invalid";
         }
         return ["text"=>$statusText,"phoneNumber"=>"+".$fetchPhoneNumber];
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}