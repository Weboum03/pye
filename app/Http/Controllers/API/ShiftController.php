<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserCard;
use App\Repositories\ShiftRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Shift4\Shift4Gateway as Shift4;

class ShiftController extends Controller
{
    private $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    public function accessTokenOld(Request $request)
    {
        $response = $this->shiftRepository->accessToken();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function accessToken(Request $request)
    {
        $validated = $request->validate([
            'apiKey' => 'required',
        ]);

        if ($validated['apiKey'] !== 'yGKCarXGccZ1f3NzcFEwFp5SC1S1gwK9cSCfrHp6') {
            return response()->json(['error' => 'Please pass a valid API key'], 403);
        }
        $currentDateTime = date('Y-m-d\TH:i:s.vP');

        $url = 'https://api.shift4test.com/api/rest/v1/credentials/accesstoken';

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 46FFB7A9-FE50-5EDD-B9D59280EBFAB865',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS'
        ];

        $data = [
            'dateTime' => $currentDateTime,
            'credential' => [
                'authToken' => '46FFB7A9-FE50-5EDD-B9D59280EBFAB865',
                'clientGuid' => 'EC39FB94-B0E8-1605-92B7CB65250EBA33'
            ]
        ];

        $response = Http::withHeaders($headers)->post($url, $data);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'error' => 'Failed to obtain access token',
                'details' => $response->json()
            ], $response->status());
        }
    }

    public function saleOld(Request $request)
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

        $response = $this->shiftRepository->createSale();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }


    /* Sale API Start */

    public function sale(Request $request)
    {
        // Collect input data
        $input = $request->all();

        $rules = [
            'amount.total' => 'required',
            'amount.tax' => 'required',
            'clerk.numericId' => 'required|integer',
            'transaction.invoice' => 'required',
            'transaction.purchaseCard.customerReference' => 'required',
            'transaction.purchaseCard.destinationPostalCode' => 'required',
            'transaction.purchaseCard.productDescriptors' => 'required|array',
            'transaction.purchaseCard.source' => 'required',
            'card.number' => 'required',
            'card.expirationDate' => 'required',
            'customer.postalCode' => 'required',
        ];

        // Validate input data
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Prepare the data array for the API request
        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $accessToken = $input['AccessToken'] ?? '';
        $data = [

            'dateTime' => $currentDateTime,
            'amount' => [
                'cashback' => $input['amount']['cashback'] ?? null,
                'tax' => $input['amount']['tax'],
                'tip' => $input['amount']['tip'] ?? null,
                'total' => $input['amount']['total'],
            ],
            'apiOptions' => $input['apiOptions'],
            'card' => [
                'entryMode' => $input['card']['entryMode'] ?? null,
                'expirationDate' => $input['card']['expirationDate'],
                'number' => $input['card']['number'],
                'present' => $input['card']['present'] ?? null,
                'securityCode' => [
                    'indicator' => '1',
                    'value' => '333',
                ],
                'type' => $input['card']['type'] ?? null,
            ],
            'clerk' => [
                'numericId' => $input['clerk']['numericId'],
            ],
            'customer' => [
                'addressLine1' => $input['customer']['addressLine1'] ?? null,
                'firstName' => $input['customer']['firstName'] ?? null,
                'lastName' => $input['customer']['lastName'] ?? null,
                'postalCode' => $input['customer']['postalCode'],
            ],
            'transaction' => [
                'hotel' => [
                    'additionalCharges' => [
                        'giftShop' => $input['transaction']['hotel']['additionalCharges']['giftShop'] ?? null,
                        'laundry' => $input['transaction']['hotel']['additionalCharges']['laundry'] ?? null,
                        'miniBar' => $input['transaction']['hotel']['additionalCharges']['miniBar'] ?? null,
                        'other' => $input['transaction']['hotel']['additionalCharges']['other'] ?? null,
                        'restaurant' => $input['transaction']['hotel']['additionalCharges']['restaurant'] ?? null,
                        'telephone' => $input['transaction']['hotel']['additionalCharges']['telephone'] ?? null,
                    ],
                    'arrivalDateTime' => $input['transaction']['hotel']['arrivalDateTime'] ?? null,
                    'departureDateTime' => $input['transaction']['hotel']['departureDateTime'] ?? null,
                    'primaryChargeType' => $input['transaction']['hotel']['primaryChargeType'] ?? null,
                    'roomRates' => $input['transaction']['hotel']['roomRates'] ?? [],
                    'specialCode' => $input['transaction']['hotel']['specialCode'] ?? null,
                ],
                'invoice' => $input['transaction']['invoice'],
                'notes' => $input['transaction']['notes'] ?? null,
                'purchaseCard' => [
                    'customerReference' => $input['transaction']['purchaseCard']['customerReference'],
                    'source' => $input['transaction']['purchaseCard']['source'],
                    'destinationPostalCode' => $input['transaction']['purchaseCard']['destinationPostalCode'],
                    'productDescriptors' => $input['transaction']['purchaseCard']['productDescriptors'] ?? [],
                ],
            ],
            "reportingData" => [
                "customerInfo" => [
                    [
                        "firstName" => $input['reportingData']['customerInfo'][0]['firstName'] ?? null,
                        "lastName" => $input['reportingData']['customerInfo'][0]['lastName'] ?? null,
                        "dateOfBirth" => $input['reportingData']['customerInfo'][0]['dateOfBirth'] ?? null,
                        "gender" => $input['reportingData']['customerInfo'][0]['gender'] ?? null,
                        "baggage" => $input['reportingData']['customerInfo'][0]['baggage'] ?? null,
                        "seats" => $input['reportingData']['customerInfo'][0]['seats'] ?? null,
                        "boardingPriority" => $input['reportingData']['customerInfo'][0]['boardingPriority'] ?? null,
                    ],
                    [
                        "firstName" => $input['reportingData']['customerInfo'][1]['firstName'] ?? null,
                        "lastName" => $input['reportingData']['customerInfo'][1]['lastName'] ?? null,
                        "dateOfBirth" => $input['reportingData']['customerInfo'][1]['dateOfBirth'] ?? null,
                        "gender" => $input['reportingData']['customerInfo'][1]['gender'] ?? null,
                        "baggage" => $input['reportingData']['customerInfo'][1]['baggage'] ?? null,
                        "seats" => $input['reportingData']['customerInfo'][1]['seats'] ?? null,
                        "boardingPriority" => $input['reportingData']['customerInfo'][1]['boardingPriority'] ?? null,
                    ],
                ],
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $accessToken,
        ])->post('https://api.shift4test.com/api/rest/v1/transactions/sale', $data);

        if ($response->failed()) {
            return $this->sendError($response->json('result.error.longText'));
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }

    /* Sale API End */


    public function invoiceOld($invoiceId, Request $request)
    {
        $response = $this->shiftRepository->invoice($invoiceId);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }


    /*Invoice APi Start*/
    public function invoice(Request $request)
    {
        $input = $request->all();


        $invoice = $input['Invoice'] ?? '';
        $accessToken = $input['AccessToken'] ?? '';

        $url = 'https://api.shift4test.com/api/rest/v1/transactions/invoice';

        $headers = [
            'Invoice' => $invoice,
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $accessToken,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json(['error' => 'Failed to fetch invoice details'], $response->status());
        }
    }

    /*Invoice APi End*/

    public function refundOld(Request $request)
    {
        $response = $this->shiftRepository->refund();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }


    /*refund Api start*/
    public function refund(Request $request)
    {
        // Collect input data
        $input = $request->all();

        // Define validation rules
        $rules = [
            'amount.tax' => 'required',
            'amount.total' => 'required',
            'card.entryMode' => 'required',
            'card.expirationDate' => 'required',
            'card.number' => 'required',
            'card.present' => 'required',
            'card.type' => 'required',
            'clerk.numericId' => 'required',
            'customer.addressLine1' => 'required',
            'customer.firstName' => 'required',
            'customer.lastName' => 'required',
            'customer.postalCode' => 'required',
            'transaction.invoice' => 'required',
            'transaction.notes' => 'required',
            'reportingData.customerInfo.*.firstName' => 'required',
            'reportingData.customerInfo.*.lastName' => 'required',
            'reportingData.customerInfo.*.dateOfBirth' => 'required',
            'reportingData.customerInfo.*.gender' => 'required',
            'reportingData.customerInfo.*.baggage' => 'required',
            'reportingData.customerInfo.*.seats' => 'required',
            'reportingData.customerInfo.*.boardingPriority' => 'required',
        ];

        // Validate input data
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Prepare the data array for the API request
        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            'dateTime' => $currentDateTime,
            'amount' => [
                'tax' => $input['amount']['tax'] ?? null,
                'total' => $input['amount']['total'] ?? null,
            ],
            'card' => [
                'entryMode' => $input['card']['entryMode'] ?? null,
                'expirationDate' => $input['card']['expirationDate'] ?? null,
                'number' => $input['card']['number'] ?? null,
                'present' => $input['card']['present'] ?? null,
                'type' => $input['card']['type'] ?? null,
            ],
            'clerk' => [
                'numericId' => $input['clerk']['numericId'] ?? null,
            ],
            'customer' => [
                'addressLine1' => $input['customer']['addressLine1'] ?? null,
                'firstName' => $input['customer']['firstName'] ?? null,
                'lastName' => $input['customer']['lastName'] ?? null,
                'postalCode' => $input['customer']['postalCode'] ?? null,
            ],
            'transaction' => [
                'invoice' => $input['transaction']['invoice'] ?? null,
                'notes' => $input['transaction']['notes'] ?? null,
            ],
            'reportingData' => [
                'customerInfo' => $input['reportingData']['customerInfo'] ?? [],
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $input['AccessToken'] ?? 'D945B773-DB7E-489F-BF18-DBDAD78F7681',
        ])->post('https://api.shift4test.com/api/rest/v1/transactions/refund', $data);

        // Check the response
        if ($response->failed()) {
            return response()->json(['error' => $response->json('result.error.longText')], 400);
        }

        return response()->json($response->json('result'), 200);
    }


    /*Refund Api End*/

    public function tokenAddOld(Request $request)
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


    /*Token add API start*/
    public function tokenAdd(Request $request)
    {
        // Collect input data
        $input = $request->all();

        // Define validation rules
        $rules = [
            'AccessToken' => 'required|string',
            'card.number' => 'required|string',
            'card.expirationDate' => 'required|integer',
        ];

        // Validate input data
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Prepare the data array for the API request
        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            'dateTime' => $currentDateTime,
            'card' => [
                'number' => $input['card']['number'],
                'expirationDate' => $input['card']['expirationDate'],
                'securityCode' => [
                    'indicator' => $input['card']['securityCode']['indicator'],
                    'value' => $input['card']['securityCode']['value'],
                ],
            ],
            'apiOptions' => ["RETURNEXPDATE"],
            'customer' => [
                'addressLine1' => $input['customer']['addressLine1'] ?? null,
                'firstName' => $input['customer']['firstName'] ?? null,
                'lastName' => $input['customer']['lastName'] ?? null,
                'postalCode' => $input['customer']['postalCode'] ?? null,
            ],
        ];

        // Make the POST request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $input['AccessToken'],
        ])->post('https://api.shift4test.com/api/rest/v1/tokens/add', $data);

        // Check the response
        if ($response->failed()) {
            return response()->json(['error' => $response->json('result.error.longText')], 400);
        }

        return response()->json($response->json('result'), 200);
    }

    /*Token add API End*/

    public function voidOld(Request $request)
    {
        $response = $this->shiftRepository->void();

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        return $this->sendResponse($response->json('result'), __('success'));
    }

    public function void(Request $request)
    {
        $input = $request->all();


        $invoice = $input['Invoice'] ?? '';
        $accessToken = $input['AccessToken'] ?? '';

        $url = 'https://api.shift4test.com/api/rest/v1/transactions/invoice';

        $headers = [
            'Invoice' => $invoice,
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $accessToken,
        ];

        $response = Http::withHeaders($headers)->delete($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json(['error' => 'Failed to fetch invoice details'], $response->status());
        }
    }

    public function saveTokenizedCard(Request $request)
    {

        // Prepare the data array for the API request
        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "card" => [
                "number" => $request->number,
                "expirationDate" => $request->expMonth.$request->expYear,
                "securityCode" => [
                    "indicator" => "1",
                    "value" => $request->cvc,
                ]
            ],
            "apiOptions" => [
                "RETURNEXPDATE"
            ],
            "customer" => [
                "addressLine1" => "65 Easy St",
                "firstName" => "John",
                "lastName" => "Smith",
                "postalCode" => "65144"
            ]
        ];

        // Make the POST request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => $input['AccessToken'],
        ])->post('https://api.shift4test.com/api/rest/v1/tokens/add', $data);

        // Check the response
        if ($response->failed()) {
            return response()->json(['error' => $response->json('result.error.longText')], 400);
        }

        try {
            $client = new Shift4(env('SHIFT4_SECRET_KEY'));

            // Tokenize card information
            $token = $client->createToken([
                'number' => $request->number,
                'expMonth' => $request->expMonth,
                'expYear' => $request->expYear,
                'cvc' => $request->cvc,
                'cardholderName' => $request->cardholderName,
            ]);

            // Save card info to the database
            $userCard = UserCard::create([
                'user_id' => $request->user()->id,  // Get the authenticated user's ID
                'shift4_token' => $token['id'],     // Tokenized card ID
                'last_four' => substr($request->number, -4),  // Last 4 digits
                'card_brand' => $token['card']['brand'],  // Card brand (from token data)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card saved successfully.',
                'user_card' => $userCard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getSavedCards(Request $request)
    {
        $cards = UserCard::where('user_id', $request->user()->id)->get();

        return response()->json([
            'success' => true,
            'cards' => $cards
        ]);
    }

    public function deleteSavedCard($cardId, Request $request)
    {
        $cards = UserCard::where('user_id', $request->user()->id)->where('id', $cardId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
