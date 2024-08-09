<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'order_id' => $this->order_id,
            'transaction_id' => $this->transaction_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'type' => $this->type,
            'card_number' => $this->card_number,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'cvc' => $this->cvc,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
