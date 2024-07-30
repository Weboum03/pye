<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\ShiftRepository;
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
        $response = $this->shiftRepository->createSale();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }
        
        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function invoice(Request $request)
    {
        $response = $this->shiftRepository->invoice();

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