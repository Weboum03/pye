<?php

namespace App\Http\Controllers\Admin;

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
        $orders = Order::latest()->get();
        return view('admins.orders.index', [
            'orders' => $orders
        ]);
    }

    public function show($id, Request $request): View
    {
        $order = Order::find($id);
        return view('admins.orders.update', [
            'order' => $order,
        ]);
    }

    public function update($id, Request $request): RedirectResponse
    {
        $user = Order::find($id);
        $user->fill($request->all());
        $user->save();

        return Redirect::route('admin.orders')->with('status', 'orders-updated');
    }
}
