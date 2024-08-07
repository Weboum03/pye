<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Admin;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $transactions = Auth::user()->transactions;
        return view('transactions.index', [
            'transactions' => $transactions
        ]);
    }

    public function show($id, Request $request): View
    {
        $transaction = Auth::user()->transactions()->where('id', $id)->first();
        return view('transactions.update', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        $transaction = Transaction::find($id);
        $transaction->fill($request->all());
        $transaction->save();

        return Redirect::route('transactions')->with('status', 'profile-updated');
    }

    public function refund(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Initiate refund logic here (e.g., using Stripe, PayPal, etc.)
        // For demonstration, we'll assume the refund is successful and update the transaction record.

        $transaction->update(['status' => 'refunded']);
        
        return redirect()->route('orders.show', $transaction->order_id)->with('success', 'Refund successful!');
    }
}
