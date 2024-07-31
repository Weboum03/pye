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

    public function getDidRequest($id) {
        return DidNumberRequest::find($id);
    }

    public function getDidwwDidId($didNumber) {
        $didNumber = str_replace('+','',$didNumber);
        $url = 'dids?filter[number]=' . $didNumber.'&include=did_group.city';
        return $this->callDidwwGetRequest($url);
    }

    public function getCountryByIso($iso) {
        $url = 'countries?filter[iso]='.$iso;
        return $this->callDidwwGetRequest($url);
    }

    public function create($data) {
        $postData = [
            'data' => [
                'type' => 'identities',
                'attributes' => [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'identity_type' => $data['identity_type'],
                ]
            ]
        ];

        if($data['identity_type'] == 'Business') {
            $postData['data']['attributes']['company_name'] = $data['company_name'];
            $postData['data']['attributes']['company_reg_number'] = $data['company_reg_number'];
            $postData['data']['attributes']['description'] = $data['description'];
            $postData['data']['attributes']['vat_id'] = $data['vat_id'];
            $postData['data']['attributes']['personal_tax_id'] = $data['personal_tax_id'];
            //$postData['data']['attributes']['id_number'] = '11111111-1111-1111-1111-111111111111';
            //$postData['data']['attributes']['birth_date'] = $data['birth_date'];
            //$postData['data']['attributes']['external_reference_id'] = $data['external_reference_id'];

            $postData['data']['relationships'] = [
                'country' => [
                    'data' => [
                        'id' => $data['country_id'],
                        'type' => 'countries'
                    ]
                ]
            ];
        }

        return $this->callDidwwPostRequest('identities', $postData);
    }

    public function update($id, $data) {
        $postData = [
            'data' => [
                'id' => $id,
                'type' => 'identities',
                'attributes' => [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                ]
            ]
        ];

        if($data['identity_type'] == 'Business') {
            $postData['data']['attributes']['company_name'] = $data['company_name'];
            $postData['data']['attributes']['company_reg_number'] = $data['company_reg_number'];
            $postData['data']['attributes']['description'] = $data['description'];
            $postData['data']['attributes']['vat_id'] = $data['vat_id'];
            $postData['data']['attributes']['personal_tax_id'] = $data['personal_tax_id'];
        }
        return $this->callDidwwPatchRequest('identities/' . $id.'?include=addresses', $postData);
    }

    public function addressCreate($data) {
        $postData = [
            'data' => [
                'type' => 'addresses',
                'attributes' => [
                    'city_name' => $data['city_name'],
                    'postal_code' => $data['postal_code'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                ],
                'relationships' => [
                    'identity' => [
                        'data' => [
                            'id' => $data['identity_id'],
                            'type' => 'identities'
                        ]
                    ],
                    'country' => [
                        'data' => [
                            'id' => $data['country_id'],
                            'type' => 'countries'
                        ]
                    ],
                ]
            ]
        ];

        return $this->callDidwwPostRequest('addresses', $postData);
    }

    public function addressUpdate($id, $data) {
        $postData = [
            'data' => [
                'id' => $id,
                'type' => 'addresses',
                'attributes' => [
                    'city_name' => $data['city_name'],
                    'postal_code' => $data['postal_code'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                ],
                'relationships' => [
                    'country' => [
                        'data' => [
                            'id' => $data['country_id'],
                            'type' => 'countries'
                        ]
                    ],
                ]
            ]
        ];

        return $this->callDidwwPatchRequest('addresses/'.$id, $postData);
    }

    public function addressVerification($data) {
        $postData = [
            'data' => [
                'type' => 'address_verifications',
                'attributes' => [
                    'callback_url' => url('admin/kyc/callback'),
                    'callback_method' => 'POST',
                ],
                'relationships' => [
                    'dids' => [
                        'data' => [
                            [
                                'id' => $data['did_id'],
                                'type' => 'dids'
                            ]
                           
                        ]
                    ],
                    'address' => [
                        'data' => [
                            'id' => $data['address_id'],
                            'type' => 'addresses'
                        ]
                    ],
                ]
            ]
        ];

        return $this->callDidwwPostRequest('address_verifications', $postData);
    }

    public function createProofs($data) {
        $files = $data['files'];
        $postData = [
            'data' => [
                'type' => 'proofs',
                'relationships' => [
                    'proof_type' => [
                        'data' => [
                            'id' => $data['proof_type_id'],
                            'type' => 'proof_types'
                        ]
                    ],
                    'entity' => [
                        'data' => [
                            'id' => $data['identity_id'],
                            'type' => 'identities'
                        ]
                    ],
                ]
            ]
        ];

        if($files) {
            foreach($files as $file) {
                $postData['data']['relationships']['files']['data'][] = ['id' => $file, 'type' => 'encrypted_files'];
            }
        }

        return $this->callDidwwPostRequest('proofs', $postData);
    }

    public function createAddressProofs($data) {
        $files = $data['files'];
        $postData = [
            'data' => [
                'type' => 'proofs',
                'relationships' => [
                    'proof_type' => [
                        'data' => [
                            'id' => $data['proof_type_id'],
                            'type' => 'proof_types'
                        ]
                    ],
                    'entity' => [
                        'data' => [
                            'id' => $data['address_id'],
                            'type' => 'addresses'
                        ]
                    ],
                ]
            ]
        ];

        if($files) {
            foreach($files as $file) {
                $postData['data']['relationships']['files']['data'][] = ['id' => $file, 'type' => 'encrypted_files'];
            }
        }

        return $this->callDidwwPostRequest('proofs', $postData);
    }

    public function createRequirementValidations($data) {
        $postData = [
            'data' => [
                'type' => 'requirement_validations',
                'relationships' => [
                    'requirement' => [
                        'data' => [
                            'id' => $data['requirement_id'],
                            'type' => 'requirements'
                        ]
                    ],
                    'address' => [
                        'data' => [
                            'id' => $data['address_id'],
                            'type' => 'addresses'
                        ]
                    ],
                    'identity' => [
                        'data' => [
                            'id' => $data['identity_id'],
                            'type' => 'identities'
                        ]
                    ],
                ]
            ]
        ];

        return $this->callDidwwPostRequest('requirement_validations', $postData);
    }

    public function listing($request) {
        return $this->callDidwwGetRequest('identities');
    }

    public function getProofTypes($entityType = 'Personal') {
        return $this->callDidwwGetRequest('proof_types?filter[entity_type]='.$entityType);
    }

    public function getRequirements($countryId) {
        return $this->callDidwwGetRequest('requirements?filter[country.id]=' . $countryId);
    }

    public function getKycVerification($verificationId) {
        return $this->callDidwwGetRequest('address_verifications/'.$verificationId.'?include=address,dids');
    }

    public function getSupportingDocumenTemplates() {
        return $this->callDidwwGetRequest('supporting_document_templates');
    }

    public function delete($identityId) {

        return $this->callDidwwDeleteRequest('identities/'.$identityId);
    }

    public function createSale() {

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
                "expirationDate" => 1230,
                "number" => "4321000000001119",
                "present" => "N",
                "securityCode" => [
                    "indicator" => "1",
                    "value" => "333"
                ],
                "type" => "VS"
            ],
            "clerk" => [
                "numericId" => 1576
            ],
            "customer" => [
                "addressLine1" => "65 Easy St",
                "firstName" => "John",
                "lastName" => "Smith",
                "postalCode" => "65144"
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

    public function refund() {

        $currentDateTime = date('Y-m-d\TH:i:s.vP');
        $data = [
            "dateTime" => $currentDateTime,
            "amount" => [
                "tax" => 10.05,
                "total" => 121.83
            ],
            "card" => [
                "entryMode" => "M",
                "expirationDate" => 1230,
                "number" => "4321000000001119",
                "present" => "N",
                "type" => "VS"
            ],
            "clerk" => [
                "numericId" => 1576
            ],
            "customer" => [
                "addressLine1" => "65 Easy St",
                "firstName" => "John",
                "lastName" => "Smith",
                "postalCode" => "65144"
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
