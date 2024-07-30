<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ApiKeyController extends Controller
{
    public function index()
    {
        $apiKeys = Auth::user()->apiKeys;
        return response()->json($apiKeys);
    }

    public function generate()
    {
        $key = ApiKey::generate();
        $apiKey = Auth::user()->apiKeys()->create(['key' => $key]);
        
        return Redirect::route('profile.edit')->with('status', 'key-updated');
        return response()->json(['key' => $apiKey->key], 201);
    }

    public function revoke($id)
    {
        $apiKey = Auth::user()->apiKeys()->findOrFail($id);
        $apiKey->delete();

        return response()->json(['message' => 'API key revoked']);
    }
}

