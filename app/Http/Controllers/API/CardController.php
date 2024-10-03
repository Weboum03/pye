<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TransactionResource;
use App\Models\ApiKey;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\UserCard;
use App\Repositories\ShiftRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Shift4\Shift4;
use Shift4\Shift4Gateway;
use Shift4\Exception\Shift4Exception;

class CardController extends Controller
{
    private $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }
    
    public function create(Request $request)
    {

        $input = $request->all();
        $rules = [
            'firstName'    => 'required',
            'lastName'    => 'required',
            'card'    => 'required|string'
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }


        if(!base64_decode($input['card'], true)) {
            return $this->sendError('Invalid card encryption');
        }

        $cardDetail = json_decode(base64_decode($input['card'], true), true);
        $rules = [
            'number'    => 'required',
            'expMonth'    => 'required',
            'expYear'    => 'required',
            'cvc'    => 'required|string',
        ];

        $validator = Validator::make($cardDetail, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $input = [...$input,...$cardDetail];

        // $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }
        
        $response = $this->shiftRepository->tokenAdd($input);

        if ($response->failed()) {
            return $this->sendError($response->collect('result'));
        }


        $responseData = collect($response->json('result'))->first();

        // Save card info to the database
        $userCard = UserCard::create([
            'user_id' => $request->user()->id,
            'card_id' => $responseData['card']['token']['value'],
            'number' => $responseData['card']['number'],
            'card_brand' => $responseData['card']['brand'],
        ]);

        return $this->sendResponse($userCard, __('ApiMessage.success'));
    }

    public function getSavedCards(Request $request)
    {
        $cards = UserCard::where('user_id', $request->user()->id)->get();

        return $this->sendResponse($cards, __('ApiMessage.success'));
    }

    public function deleteSavedCard($cardId, Request $request)
    {
        $cards = UserCard::where('user_id', $request->user()->id)->where('id', $cardId)->delete();

        return $this->sendResponse($cards, __('ApiMessage.success'));
    }

}
