<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Admin;
use App\Models\Merchant;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class MerchantController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $users = Merchant::latest()->get();
        return view('admins.merchants.content', [
            'user' => $request->user(),
            'users' => $users
        ]);
    }

    public function show($id, Request $request): View
    {
        $users = Merchant::latest()->get();
        $user = Merchant::find($id);
        return view('admins.merchants.content', [
            'user' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Merchant::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Merchant::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        return back()->with('status', 'user-created');
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        $user = Merchant::find($id);
        $user->fill($request->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('admin.merchants')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
