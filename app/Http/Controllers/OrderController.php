<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Admin;
use App\Models\Merchant;
use App\Models\Order;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function edit(Request $request): View
    {
        $orders = Auth::user()->orders;
        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    public function show($id, Request $request): View
    {
        $order = Auth::user()->orders()->where('id', $id)->first();
        return view('orders.update', [
            'order' => $order,
        ]);
    }

    public function update($id, Request $request): RedirectResponse
    {
        $user = Order::find($id);
        $user->fill($request->all());
        $user->save();

        return Redirect::route('orders')->with('status', 'orders-updated');
    }
}
