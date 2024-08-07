<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Order;
use App\Models\Transaction;
use App\Repositories\ShiftRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Shift4\Shift4;
use Shift4\Shift4Gateway;
use Shift4\Exception\Shift4Exception;

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
            'amount'    => 'required',
            'number'    => 'required',
            'exp_month'    => 'required',
            'cvc'    => 'required',
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

        if(!$merchantId) {
            return $this->sendError('Invalid API Key');
        }

        $order = Order::create([
            'order_id' => Str::uuid(),
            'merchant_id' => $merchantId,
            'user_id' => 1,
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
        
        $response = $this->shiftRepository->createSale($input);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        $order->update(['status' => 'completed']);

        return $this->sendResponse($order, __('ApiMessage.success'));
    }

    public function show($id)
    {
        $order = Order::findOrFail($id);
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

        if(!$order->transactions()->exists()) {
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

        return $this->sendResponse($transaction, __('ApiMessage.success'));
    }

    public function refund($transactionId, Request $request)
    {
        $transaction = Transaction::find($transactionId);

        if(!$transaction) {
            return $this->sendError('Transaction not found');
        }

        $order = $transaction->order;
        $response = $this->shiftRepository->refund($order);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        $transaction->status = 'refund';
        $transaction->save();
        
        return $this->sendResponse($response->json('result'), __('success'));
    }
}
