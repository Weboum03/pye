<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        if($request->has('from') && $request->has('to')) {
            $users = User::latest()->whereBetween('created_at', [$request->from, $request->to])->get();
        } else {
            $users = User::latest()->get();
        }
        
        return view('admins.users.content', [
            'user' => $request->user('admin'),
            'users' => $users
        ]);
    }

    public function show($id, Request $request): View
    {
        $users = User::latest()->get();
        $user = User::find($id);
        return view('admins.users.content', [
            'user' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        return back()->with('status', 'user-created');
        return redirect('admin.users');
    }

    /**
     * Update the user's profile information.
     */
    public function update($id, Request $request): RedirectResponse
    {
        // dd($request->all());
        $user = User::find($id);
        $user->fill($request->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        // dd($user);
        $user->save();

        return Redirect::route('admin.users')->with('status', 'profile-updated');
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
