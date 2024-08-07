<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Http;
use App\Models\CallRate;
use App\Models\Customer;
use App\Models\DidNumber;
use App\Models\DidNumberRequest;
use Didww\Credentials;
use Didww\Encrypt;
use Didww\Item\EncryptedFile;
use Didww\Configuration;
/**
 * Class IdentityRepository
 * @package App\Repositories
 * @version July 31, 2023, 7:13 am UTC
*/

class ShiftRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return CallRate::class;
    }

    public function createSale($payload) {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "amount" => [
                "cashback" => 20,
                "tax" => 15,
                "tip" => 20,
                "total" => 160
            ],
            "apiOptions" => [
                "ALLOWPARTIALAUTH"
            ],
            "card" => [
                "entryMode" => "M",
                "expirationDate" => $payload['exp_month'].$payload['exp_year'],
                "number" => $payload['number'],
                "present" => "N",
                "securityCode" => [
                    "indicator" => "1",
                    "value" => $payload['cvc'],
                ],
                "type" => "VS"
            ],
            "clerk" => [
                "numericId" => 1576
            ],
            "customer" => [
                "addressLine1" => $payload['address'],
                "firstName" => $payload['first_name'],
                "lastName" => $payload['last_name'],
                "postalCode" => $payload['postal_code'],
            ],
            "transaction" => [
                "hotel" => [
                    "additionalCharges" => [
                        "giftShop" => "Y",
                        "laundry" => "Y",
                        "miniBar" => "Y",
                        "other" => "Y",
                        "restaurant" => "Y",
                        "telephone" => "Y"
                    ],
                    "arrivalDateTime" => "2021-04-12T15:39:01.594-07:00",
                    "departureDateTime" => "2021-04-15T09:18:23.283-07:00",
                    "primaryChargeType" => 1,
                    "roomRates" => [
                        [
                            "nights" => 2,
                            "rate" => 159.95
                        ],
                        [
                            "nights" => 3,
                            "rate" => 125.38
                        ]
                    ],
                    "specialCode" => 1
                ],
                "invoice" => "192029",
                "notes" => "Transaction notes are added here",
                "purchaseCard" => [
                    "customerReference" => "D019D09309F2",
                    "source" => "2",
                    "destinationPostalCode" => "94719",
                    "productDescriptors" => [
                        "Hamburger",
                        "Fries",
                        "Soda",
                        "Cookie"
                    ]
                ]
            ],
            "reportingData" => [
                "customerInfo" => [
                    [
                        "firstName" => "Jane",
                        "lastName" => "Smith",
                        "dateOfBirth" => "12011983",
                        "gender" => "female",
                        "baggage" => "checked",
                        "seats" => "1A",
                        "boardingPriority" => "1"
                    ],
                    [
                        "firstName" => "John",
                        "lastName" => "Smith",
                        "dateOfBirth" => "01281980",
                        "gender" => "male",
                        "baggage" => "carryon",
                        "seats" => "1B",
                        "boardingPriority" => "1"
                    ]
                ]
            ]
        ];
        
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => 'D945B773-DB7E-489F-BF18-DBDAD78F7681'
        ])->post('https://api.shift4test.com/api/rest/v1/transactions/sale', $data);
    }

    public function refund($payload) {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "amount" => [
                "tax" => 10.05,
                "total" => 121.83
            ],
            "card" => [
                "entryMode" => "M",
                "expirationDate" => $payload->exp_month.$payload->exp_year,
                "number" => $payload->card_number,
                "present" => "N",
                "type" => "VS"
            ],
            "clerk" => [
                "numericId" => 1576
            ],
            "customer" => [
                "addressLine1" => $payload->address,
                "firstName" => $payload->first_name,
                "lastName" => $payload->last_name,
                "postalCode" => $payload->postal_code,
            ],
            "transaction" => [
                "invoice" => "192029",
                "notes" => "Transaction notes are added here"
            ],
            "reportingData" => [
                "customerInfo" => [
                    [
                        "firstName" => "Jane",
                        "lastName" => "Smith",
                        "dateOfBirth" => "12011983",
                        "gender" => "female",
                        "baggage" => "checked",
                        "seats" => "1A",
                        "boardingPriority" => "1"
                    ],
                    [
                        "firstName" => "John",
                        "lastName" => "Smith",
                        "dateOfBirth" => "01281980",
                        "gender" => "male",
                        "baggage" => "carryon",
                        "seats" => "1B",
                        "boardingPriority" => "1"
                    ]
                ]
            ]
        ];
        
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => 'D945B773-DB7E-489F-BF18-DBDAD78F7681'
        ])->post('https://api.shift4test.com/api/rest/v1/transactions/refund', $data);
    }

    public function tokenAdd() {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "card" => [
                "number" => "4321000000001119",
                "expirationDate" => 1230,
                "securityCode" => [
                    "indicator" => "1",
                    "value" => "333"
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
        
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => 'D945B773-DB7E-489F-BF18-DBDAD78F7681'
        ])->post('https://api.shift4test.com/api/rest/v1/tokens/add', $data);
    }

    public function accessToken() {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "credential" => [
                "authToken" => "46FFB7A9-FE50-5EDD-B9D59280EBFAB865",
                "clientGuid" => "EC39FB94-B0E8-1605-92B7CB65250EBA33"
            ]
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer 46FFB7A9-FE50-5EDD-B9D59280EBFAB865',
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS'
        ])->post('https://api.shift4test.com/api/rest/v1/credentials/accesstoken', $data);
    }

    public function invoice($invoiceId) {
        return Http::withHeaders([
            'Invoice' => $invoiceId,
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => 'D945B773-DB7E-489F-BF18-DBDAD78F7681'
        ])->get('https://api.shift4test.com/api/rest/v1/transactions/invoice');
    }

    public function void() {
        return Http::withHeaders([
            'Invoice' => '0000192029',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => 'D945B773-DB7E-489F-BF18-DBDAD78F7681'
        ])->get('https://api.shift4test.com/api/rest/v1/transactions/invoice');
    }

    public function callGetRequest($url)
    {
        return Http::withHeaders([
            'Api-Key' => env('DID_API_KEY', config('env.DID_API_KEY', 'DID_API_KEY'))
        ])->get(env('DID_URL', 'https://api.shift4test.com/api/rest/v1/') . $url);
    }

    public function callPostRequest($url, $data)
    {
        return Http::withHeaders([
            'Api-Key' => env('DID_API_KEY', config('env.DID_API_KEY', 'DID_API_KEY')),
            'Content-Type' => 'application/vnd.api+json'
        ])->post(env('DID_URL', 'https://api.shift4test.com/api/rest/v1/') . $url, $data);
    }

    public function callDeleteRequest($url)
    {
        return Http::withHeaders([
            'Api-Key' => env('DID_API_KEY', config('env.DID_API_KEY', 'DID_API_KEY'))
        ])->delete(env('DID_URL', 'https://api.shift4test.com/api/rest/v1/') . $url);
    }

    public function callPatchRequest($url, $data)
    {
        return Http::withHeaders([
            'Api-Key' => env('DID_API_KEY', config('env.DID_API_KEY', 'DID_API_KEY')),
            'Content-Type' => 'application/vnd.api+json'
        ])->patch(env('DID_URL', 'https://api.shift4test.com/api/rest/v1/') . $url, $data);
    }
}
