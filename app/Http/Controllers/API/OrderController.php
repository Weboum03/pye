<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TransactionResource;
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
            'order_id'    => 'required|unique:orders',
            'tax'    => 'required',
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
            'order_id' => $input['order_id'],
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
        $input['id'] = (string)$order->id;
        $response = $this->shiftRepository->createSale($input);

        if ($response->failed()) {
            return $this->sendError($response->collect('result'));
        }

        $order->update(['status' => 'completed']);
        $order->transaction_id = $transaction->transaction_id;

        return $this->sendResponse(new OrderResource($order), __('ApiMessage.success'));
    }

    public function show($id)
    {
        $order = Order::where('order_id',$id)->first();

        if(!$order) {
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
        $transaction = Transaction::where('transaction_id',$transactionId)->first();

        if(!$transaction) {
            return $this->sendError('Transaction not found');
        }

        $order = $transaction->order;
        $response = $this->shiftRepository->refund($order);

        if ($response->failed()) {
            return $this->sendError($response->collect('result')->first()['error']['longText']);
        }

        if($transaction->status == 'refund') {
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
