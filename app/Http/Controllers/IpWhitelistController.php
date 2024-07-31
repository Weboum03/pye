<?php

namespace App\Http\Controllers;

use App\Models\IpWhitelist;
use Illuminate\Http\RedirectResponse;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class IpWhitelistController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $users = IpWhitelist::latest()->get();
        return view('whitelists.content', [
            'user' => $request->user(),
            'users' => $users
        ]);
    }

    public function show($id, Request $request): View
    {
        $users = IpWhitelist::latest()->get();
        $user = IpWhitelist::find($id);
        return view('whitelists.content', [
            'user' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:255', 'unique:'.IpWhitelist::class],
        ]);

        $ipWhitelist = Auth::user()->ipWhitelists()->create($request->all());

        return back()->with('status', 'user-created');
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        $user = IpWhitelist::find($id);
        $user->fill($request->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('ip_whitelists')->with('status', 'profile-updated');
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
