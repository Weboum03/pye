<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Models\ClearmeInfo;

class UserController extends BaseController
{

    protected $userRepository;

    /**
     * The user repository instance.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->listing();
        return $this->sendResponse($users,__('ApiMessage.retrievedMessage'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $rules = [
            'email'    => 'unique:users|required',
            'password'    => 'required',
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }
        $user = $this->userRepository->store($input);
        
        return $this->sendResponse($user, __('AdminMessage.customerAdd'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->userRepository->getByUserId($id);
        
        if (!$user) {
            return $this->sendError('User not found');
        }
        return $this->sendResponse($user, __('AdminMessage.retrievedMessage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $input = $request->all();
        $user = $this->userRepository->getByUserId($id);
        
        if (!$user) {
            return $this->sendError('User not found');
        }
        $user->fill($input)->save();

        return $this->sendResponse($user, __('AdminMessage.customerUpdate'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $input = $request->all();
        $rules = [
            'final_working_date' => 'required|after:start_date',
        ];

        $message = [
            'final_working_date.after' => 'Final Working date should be greater than De-Boarding date'
        ];
        $validator = Validator::make($input, $rules, $message);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }
        $this->userRepository->deleteById($id);
        return $this->sendSuccess(__('AdminMessage.customerDelete'));
    }
    
      /*Token API */
    
    public function generateToken(Request $request)
    {
        $client = new Client();

        $response = $client->post('https://verified.clearme.com/v1/verification_sessions', [
            'json' => [
                'redirect_url' => 'https://stage.paypye.com/clearme/profile.php',
                'is_webview' => false,
                'project_id' => 'project_Yu1KCOljV51pGVlwtNqqJoGLFMSd6XTL36hfbXYhgs',
            ],
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer sandbox_Cx4hD36c6QSXGqAfrI2944zp434hCJVRT',
                'content-type' => 'application/json',
            ],
        ]);

        $jsonResponse = json_decode($response->getBody(), true);
        $verifyToken = $jsonResponse['token'];
        $verifyTokenId = $jsonResponse['id'];

        // Store the verification token ID in the session
        //Session::put('verificationTokenId', $verifyTokenId);
        
        $clearmeInfo = ClearmeInfo::create([
            'verification_session_id' => $verifyTokenId,
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'dob' => '',
            'status' => '',
        ]);

        return response()->json([
            'token' => $verifyToken,
            'id' => $clearmeInfo->id,
        ]);
    }
    
    /*Api For getting the user data on the basis of verification session id*/
    
    public function fetchVerificationSession(Request $request)
    {
        $id = 11;
        $clearmeInfo = ClearmeInfo::find($id);
        
        if (!$clearmeInfo) {
            return response()->json(['error' => 'ClearmeInfo not found'], 404);
        }
        
        $verificationSessionId = $clearmeInfo->verification_session_id;
        
        
        $client = new Client();

        $response = $client->request('GET', 'https://verified.clearme.com/v1/verification_sessions/' . $verificationSessionId, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer sandbox_Cx4hD36c6QSXGqAfrI2944zp434hCJVRT',
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        $clearmeInfo->first_name = $body['verified_info']['first_name'] ?? '';
        $clearmeInfo->middle_name = $body['verified_info']['middle_name'] ?? '';
        $clearmeInfo->last_name = $body['verified_info']['last_name'] ?? '';
        $clearmeInfo->email = $body['verified_info']['email'] ?? '';
        $clearmeInfo->phone = $body['verified_info']['phone'] ?? '';
        
        if (isset($body['verified_info']['dob'])) {
            $year = $body['verified_info']['dob']['year'] ?? '';
            $month = $body['verified_info']['dob']['month'] ?? '';
            $day = $body['verified_info']['dob']['day'] ?? '';

            if ($year && $month && $day) {
                $dob = "$year-$month-$day";
            } else {
                $dob = '';
            }
        } else {
            $dob = '';
        }
        $clearmeInfo->dob = $dob;
        $clearmeInfo->status = $body['status'] ?? '';
 
        $clearmeInfo->save();
        
        if ($clearmeInfo->status === 'success') {
             if (empty($clearmeInfo->phone)) {
                return response()->json(['error' => 'Phone number is required.'], 400);
            }
           // $user = User::where('email', $clearmeInfo->email)->first();
            $user = User::where('phone', $clearmeInfo->phone)->first();
            if (!$user) {
                
                $user = new User();
                $user->name = trim(($body['verified_info']['first_name'] ?? ''));
                $user->email = $clearmeInfo->email;
                $date = date('YmdHis');
                $randomNumber = mt_rand(100000, 999999);
                $password = $date . $randomNumber;
                
                $user->password = $password;
                $user->phone = $body['verified_info']['phone'] ?? '';
                $user->dob = $clearmeInfo->dob;
                
                $user->save();
            }
            $token = auth('api')->login($user);
            //$clearmeInfo->access_token = $token;
            if ($token) {
                $response = ['access_token' => $token];
                return response()->json($response, 200);
            }
        }
        
        return response()->json(['error' => 'Unauthorized'], 401);
    
    }
    
}
