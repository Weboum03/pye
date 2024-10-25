<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Http;
use App\Models\CallRate;
use App\Models\Customer;
use App\Models\DidNumber;
use App\Models\DidNumberRequest;
use App\Models\UserCard;
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

    public function createSale($payload, $type = 'card')
    {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "amount" => [
                "tax" => $payload['tax'],
                "total" => $payload['amount'],
            ],
            "apiOptions" => [
                "ALLOWPARTIALAUTH"
            ],
            "clerk" => [
                "numericId" => 1576
            ],
            "transaction" => [
                "invoice" => $payload['id'],
                "notes" => "Transaction notes are added here",
                "purchaseCard" => [
                    "customerReference" => "D019D09309F2",
                    "destinationPostalCode" => "94719",
                    "productDescriptors" => [
                        "Hamburger",
                        "Fries",
                        "Soda",
                        "Cookie"
                    ]
                ],
                "source" => "2",
            ],
            "customer" => [
                "addressLine1" => $payload['address'],
                "firstName" => $payload['first_name'],
                "lastName" => $payload['last_name'],
                "postalCode" => $payload['postal_code'],
            ],

        ];

        if ($type == 'savedCard') {
            $userCard = UserCard::where('card_id', $payload['card_id'])->first();
            $data['card'] = [
                "token" => [
                    "value" => (string)$userCard->card_id
                ]
            ];
        } 
        elseif($type == 'device') {
            $data['device'] = [
                'terminalId' => $payload['device_token']
            ];
        }
        else {
            $data['card'] = [
                "entryMode" => "M",
                "expirationDate" => $payload['exp_month'] . $payload['exp_year'],
                "number" => $payload['number'],
                "present" => "N",
                "securityCode" => [
                    "indicator" => "1",
                    "value" => $payload['cvc'],
                ],
                "type" => "VS"
            ];
        }
        
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->post(env('SHIFT4_API_URL').'/transactions/sale', $data);
    }

    public function refund($payload)
    {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "amount" => [
                "tax" => 0,
                "total" => (int)$payload->amount,
            ],
            "card" => [
                "entryMode" => "M",
                "expirationDate" => $payload->exp_month . $payload->exp_year,
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
            ]
        ];

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->post(env('SHIFT4_API_URL').'/transactions/refund', $data);
    }

    public function tokenAdd($data, $type = 'card')
    {
        
        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $payload = [
            "dateTime" => $currentDateTime,
            "apiOptions" => [
                "RETURNEXPDATE"
            ],
            "customer" => [
                "addressLine1" => "65 Easy St",
                "firstName" => $data['first_name'],
                "lastName" => $data['last_name'],
                "postalCode" => "65144"
            ]
        ];

        if($type == 'device') {
            $payload['device'] = [
                'terminalId' => $data['device_token']
            ];
        } else {
            $payload['card'] = [
                "number" => (string)$data['number'],
                "expirationDate" => (string)$data['exp_month'] . $data['exp_year'],
                "securityCode" => [
                    "indicator" => "1",
                    "value" => (string)$data['cvc']
                ] 
            ];
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->post(env('SHIFT4_API_URL').'/tokens/add', $payload);
    }

    public function deleteCardToken($data)
    {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "card" => [
                "number" => (string)$data['number'],
                "expirationDate" => (string)$data['expMonth'] . $data['expYear'],
                "securityCode" => [
                    "indicator" => "1",
                    "value" => (string)$data['cvc']
                ]
            ],
            "apiOptions" => [
                "RETURNEXPDATE"
            ],
            "customer" => [
                "addressLine1" => "65 Easy St",
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "postalCode" => "65144"
            ]
        ];

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->post(env('SHIFT4_API_URL').'/tokens/add', $data);
    }

    public function accessToken()
    {

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
        ])->post(env('SHIFT4_API_URL').'/credentials/accesstoken', $data);
    }

    public function invoice($invoiceId)
    {
        return Http::withHeaders([
            'Invoice' => $invoiceId,
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->get(env('SHIFT4_API_URL').'/transactions/invoice');
    }

    public function void()
    {
        return Http::withHeaders([
            'Invoice' => '0000192029',
            'InterfaceVersion' => '2.1',
            'InterfaceName' => 'ForwardPOS',
            'CompanyName' => 'PAWS',
            'AccessToken' => env('SHIFT4_ACCESS_TOKEN')
        ])->get(env('SHIFT4_API_URL').'/transactions/invoice');
    }
}
