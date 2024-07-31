<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\IpWhitelist;
use Illuminate\Http\RedirectResponse;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $users = Company::latest()->get();
        return view('companies.content', [
            'user' => $request->user(),
            'users' => $users
        ]);
    }

    public function show($id, Request $request): View
    {
        $users = Company::latest()->get();
        $user = Company::find($id);
        return view('companies.content', [
            'user' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Company::class],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->companies()->create($request->all());

        return back()->with('status', 'user-created');
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        $user = Company::find($id);
        $user->fill($request->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('companies')->with('status', 'profile-updated');
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
