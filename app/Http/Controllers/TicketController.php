<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Admin;
use App\Models\Merchant;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Transaction;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        if($request->has('from') && $request->has('to')) {
            $tickets = Auth::user()->tickets()->whereBetween('created_at', [$request->from, $request->to])->get();
        } else {
            $tickets = Auth::user()->tickets;
        }
        
        return view('tickets.index', [
            'tickets' => $tickets
        ]);
    }

    public function show($id, Request $request): View
    {
        $ticket = Auth::user()->tickets()->where('id', $id)->first();
        return view('tickets.update', [
            'ticket' => $ticket
        ]);
    }

    public function reply($id, Request $request): View
    {
        $ticket = Ticket::find($id);

        if($request->method() === 'POST') {
            // Validate the reply data
            $request->validate([
                'message' => 'required|string',
            ]);

            // Create the reply
            TicketReply::create([
                'ticket_id' => $ticket->id,
                'message' => $request->message,
            ]);

            // Optionally update the ticket status
            $ticket->update(['status' => 'in-progress']);
        }
        // /if($request->method())
        
        // Load the replies with the ticket
        $ticket->load('replies.user'); // Eager load replies and the users who made the replies
        
        return view('tickets.reply', [
            'ticket' => $ticket
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        $ticket = Ticket::find($id);
        $ticket->fill($request->all());
        $ticket->save();

        return Redirect::route('tickets')->with('status', 'profile-updated');
    }
}
