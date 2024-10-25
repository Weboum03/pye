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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
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
            'order_id'    => 'required|unique:orders',
            'tax'    => 'required',
            'amount'    => 'required',
            'card'    => 'required|string',
            'address'    => 'required',
            'first_name'    => 'required',
            'last_name'    => 'required',
            'postal_code'    => 'required',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        DB::beginTransaction();

        try {
            if (!base64_decode($input['card'], true)) {
                return $this->sendError('Invalid card encryption');
            } else {
                $cardDetail = json_decode(base64_decode($input['card'], true), true);
                $rules = [
                    'number'    => 'required',
                    'exp_month'    => 'required',
                    'exp_year'    => 'required|string',
                    'cvc'    => 'required',
                ];

                $validator = Validator::make($cardDetail, $rules);

                if ($validator->fails()) {
                    return $this->sendError($validator->errors()->first(), $validator->errors());
                }

                $input = [...$input, ...$cardDetail];
            }
            
            $order = Order::create([
                'order_id' => $input['order_id'],
                'merchant_id' => $merchantId,
                'user_id' => $request->user()->id,
                'total_amount' => $input['amount'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'address' => $input['address'],
                'postal_code' => $input['postal_code'],
                'type' => 'CC',
                'card_number' => $input['number'],
                'exp_month' => $input['exp_month'],
                'exp_year' => $input['exp_year'],
                'cvc' => $input['cvc'],
                'status' => 'pending'
            ]);
    
            $transaction = Transaction::create([
                'transaction_id' => Str::uuid(),
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'type' => 'capture',
                'status' => 'completed'
            ]);
            $input['id'] = (string)$order->id;
            $response = $this->shiftRepository->createSale($input, 'card');
    
            if ($response->failed()) {
                return $this->sendError('Something went wrong.',$response->collect());
            }
    
            $order->update(['status' => 'completed']);
            $order->transaction_id = $transaction->transaction_id;

            
            #if save card is true then save card
            if ($request->save_card) {
                $response = $this->shiftRepository->tokenAdd($input);

                
                if ($response->successful()) {
                    $responseData = collect($response->json('result'))->first();
                    
                    if (!UserCard::where('user_id', $request->user()->id)->where('card_id', $responseData['card']['token']['value'])->exists()) {
                        UserCard::create([
                            'user_id' => $request->user()->id,
                            'card_id' => $responseData['card']['token']['value'],
                            'number' => $responseData['card']['number'],
                            'exp_month' => substr($responseData['card']['expirationDate'], 0, 2),
                            'exp_year' => substr($responseData['card']['expirationDate'], 2, 4),
                            'card_brand' => $responseData['card']['brand'],
                        ]);
                    }
                }
            }
    
            return $this->sendResponse(new OrderResource($order), __('ApiMessage.success'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), $e);
        }
    }

    public function createGuest(Request $request)
    {
        $input = $request->all();
        $rules = [
            'order_id'    => 'required|unique:orders',
            'tax'    => 'required',
            'amount'    => 'required',
            'card'    => 'required|string',
            'address'    => 'required',
            'first_name'    => 'required',
            'last_name'    => 'required',
            'phone'    => 'required',
            'postal_code'    => 'required',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        DB::beginTransaction();

        try {
            if (!base64_decode($input['card'], true)) {
                return $this->sendError('Invalid card encryption');
            } else {
                $cardDetail = json_decode(base64_decode($input['card'], true), true);
                $rules = [
                    'number'    => 'required',
                    'exp_month'    => 'required',
                    'exp_year'    => 'required|string',
                    'cvc'    => 'required',
                ];

                $validator = Validator::make($cardDetail, $rules);

                if ($validator->fails()) {
                    return $this->sendError($validator->errors()->first(), $validator->errors());
                }

                $input = [...$input, ...$cardDetail];
            }
            
            $order = Order::create([
                'order_id' => $input['order_id'],
                'merchant_id' => $merchantId,
                'user_id' => 1,
                'total_amount' => $input['amount'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'phone' => $input['phone'],
                'address' => $input['address'],
                'postal_code' => $input['postal_code'],
                'type' => 'CC',
                'card_number' => $input['number'],
                'exp_month' => $input['exp_month'],
                'exp_year' => $input['exp_year'],
                'cvc' => $input['cvc'],
                'status' => 'pending'
            ]);
    
            $transaction = Transaction::create([
                'transaction_id' => Str::uuid(),
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'type' => 'capture',
                'status' => 'completed'
            ]);
            $input['id'] = (string)$order->id;
            $response = $this->shiftRepository->createSale($input, 'card');
    
            if ($response->failed()) {
                return $this->sendError('Something went wrong.',$response->collect());
            }
    
            $order->update(['status' => 'completed']);
            $order->transaction_id = $transaction->transaction_id;
            
            return $this->sendResponse(new OrderResource($order), __('ApiMessage.success'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), $e);
        }
    }

    public function createByCard(Request $request)
    {
        $input = $request->all();
        $rules = [
            'order_id'    => 'required|unique:orders',
            'tax'    => 'required',
            'amount'    => 'required',
            'card_id'    => 'required',
            'address'    => 'required',
            'first_name'    => 'required',
            'last_name'    => 'required',
            'postal_code'    => 'required',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        DB::beginTransaction();


        try {

            $userCard = UserCard::where('card_id', $input['card_id'])->first();

            if (!$userCard) {
                return $this->sendError('Card does not exist');
            }

            $order = Order::create([
                'order_id' => $input['order_id'],
                'merchant_id' => $merchantId,
                'user_id' => 1,
                'total_amount' => $input['amount'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'address' => $input['address'],
                'postal_code' => $input['postal_code'],
                'type' => 'CC',
                'card_number' => $userCard->number,
                'exp_month' => $userCard->exp_month,
                'exp_year' => $userCard->exp_year,
                'status' => 'pending'
            ]);
            
    
            $transaction = Transaction::create([
                'transaction_id' => Str::uuid(),
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'type' => 'capture',
                'status' => 'completed'
            ]);
            $input['id'] = (string)$order->id;
            $response = $this->shiftRepository->createSale($input, 'savedCard');
    
            if ($response->failed()) {
                return $this->sendError('Something went wrong.',$response->collect());
            }
    
            $order->update(['status' => 'completed']);
            $order->transaction_id = $transaction->transaction_id;
    
            return $this->sendResponse(new OrderResource($order), __('ApiMessage.success'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->sendError($e, $e);
        }
    }

    
    public function createByDevice(Request $request)
    {
        $input = $request->all();
        $rules = [
            'order_id'    => 'required|unique:orders',
            'tax'    => 'required',
            'amount'    => 'required',
            'device_token'    => 'required',
            'address'    => 'required',
            'first_name'    => 'required',
            'last_name'    => 'required',
            'postal_code'    => 'required',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        DB::beginTransaction();

        try {

            $order = Order::create([
                'order_id' => $input['order_id'],
                'merchant_id' => $merchantId,
                'user_id' => 1,
                'total_amount' => $input['amount'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'address' => $input['address'],
                'postal_code' => $input['postal_code'],
                'type' => 'CC',
                'status' => 'pending'
            ]);
            
    
            $transaction = Transaction::create([
                'transaction_id' => Str::uuid(),
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'type' => 'capture',
                'status' => 'completed'
            ]);
            $input['id'] = (string)$order->id;
            $response = $this->shiftRepository->createSale($input, 'device');
    
            if ($response->failed()) {
                return $this->sendError('Something went wrong.',$response->collect());
            }
    
            $order->update(['status' => 'completed']);
            $order->transaction_id = $transaction->transaction_id;
    
            if ($request->save_card) {
                $response = $this->shiftRepository->tokenAdd($input, 'device');

                
                if ($response->successful()) {
                    $responseData = collect($response->json('result'))->first();
                    
                    if (!UserCard::where('user_id', $request->user()->id)->where('card_id', $responseData['card']['token']['value'])->exists()) {
                        UserCard::create([
                            'user_id' => $request->user()->id,
                            'card_id' => $responseData['card']['token']['value'],
                            'number' => $responseData['card']['number'],
                            'exp_month' => substr($responseData['card']['expirationDate'], 0, 2),
                            'exp_year' => substr($responseData['card']['expirationDate'], 2, 4),
                            'card_brand' => $responseData['card']['brand'],
                        ]);
                    }
                }
            }

            return $this->sendResponse(new OrderResource($order), __('ApiMessage.success'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->sendError($e, $e);
        }
    }

    public function show($id)
    {
        $order = Order::where('order_id', $id)->first();

        if (!$order) {
            return $this->sendError('Order not exist');
        }
        return $this->sendResponse($order, __('ApiMessage.success'));
    }

    public function processPayment(Request $request, $id)
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

        $order = Order::findOrFail($id);

        if (!$order) {
            return $this->sendError('Order id does not exists.');
        }

        if (!$order->transactions()->exists()) {
            return $this->sendError('Order already captured.');
        }

        // Initiate payment gateway logic here (e.g., using Stripe, PayPal, etc.)



        // For demonstration, we'll assume the payment is successful and create a transaction record.

        $transaction = Transaction::create([
            'transaction_id' => Str::uuid(),
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'type' => 'capture',
            'status' => 'completed'
        ]);

        $order->update(['status' => 'completed']);

        return $this->sendResponse(new TransactionResource($transaction), __('ApiMessage.success'));
    }

    public function refund(Request $request)
    {
        $input = $request->all();
        $rules = [
            'transaction_id'    => 'required',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $transactionId = $input['transaction_id'];
        $transaction = Transaction::where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            return $this->sendError('Transaction not found');
        }

        $order = $transaction->order;
        $response = $this->shiftRepository->refund($order);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        if ($transaction->status == 'refund') {
            return $this->sendError('Already refunded.');
        }

        Transaction::create([
            'transaction_id' => Str::uuid(),
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'type' => 'refund',
            'status' => 'completed'
        ]);

        $transaction->status = 'refund';
        $transaction->save();

        $order->status = 'refund';
        $order->save();

        return $this->sendResponse(new TransactionResource($transaction), __('success'));
    }
}
