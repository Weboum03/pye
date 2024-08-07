<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\ShiftRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    public function accessToken(Request $request)
    {
        $response = $this->shiftRepository->accessToken();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function sale(Request $request)
    {
        $input = $request->all();
        $rules = [
            'number'    => 'required',
            'expirationDate'    => 'required',
            'securityCode'    => 'required',
            'addressLine1'    => 'required',
            'firstName'    => 'required',
            'lastName'    => 'required',
            'postalCode'    => 'required',
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $response = $this->shiftRepository->createSale($input);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function invoice($invoiceId, Request $request)
    {
        $response = $this->shiftRepository->invoice($invoiceId);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function refund(Request $request)
    {
        $response = $this->shiftRepository->refund();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function tokenAdd(Request $request)
    {
        $input = $request->all();
        $rules = [
            'number'    => 'required',
            'expirationDate'    => 'required',
            'securityCode'    => 'required',
            'addressLine1'    => 'required',
            'firstName'    => 'required',
            'lastName'    => 'required',
            'postalCode'    => 'required',
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }
        
        $response = $this->shiftRepository->tokenAdd();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function void(Request $request)
    {
        $response = $this->shiftRepository->void();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }
}