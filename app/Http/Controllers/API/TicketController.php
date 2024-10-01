<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }
        
        $input = $request->all();
        $rules = [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $ticket = Ticket::create([
            'subject' => $request->subject,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'merchant_id' => $merchantId,
        ]);
        return $this->sendResponse($ticket, __('success'));
    }

    /**
     * Store a newly created reply in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        $input = $request->all();

        // Validate the reply data
        $rules = [
            'message' => 'required|string',
        ];

        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        // Create the reply
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Optionally update the ticket status
        $ticket->update(['status' => 'in-progress']);

        return $this->sendResponse($ticket, __('success'));
    }

    public function getReply(Request $request, Ticket $ticket)
    {
        $merchantId = ApiKey::where('key', $request->header('API-Key'))->value('merchant_id');

        // if(!$merchantId) {
        //     return $this->sendError('Invalid API Key');
        // }

        $ticket->load('replies.user');

        return $this->sendResponse($ticket, __('success'));
    }
}
