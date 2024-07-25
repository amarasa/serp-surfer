<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_SearchConsole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoogleAuthController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(config('google.application_name'));
        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri'));
        $this->client->setScopes(config('google.scopes'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function redirectToGoogle()
    {
        $authUrl = $this->client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    public function googleConnectorProfilePage(Request $request): View
    {
        return view('profile.google', [
            'user' => $request->user(),
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->get('code')) {
            $this->client->authenticate($request->get('code'));
            $token = $this->client->getAccessToken();

            $user = Auth::user();
            $user->google_token = $token['access_token'];
            $user->google_refresh_token = $token['refresh_token'];
            $user->save();

            return redirect()->route('gsc')->with('success', 'Google Search Console connected successfully!');
        }

        return redirect()->route('gsc')->with('error', 'Failed to connect to Google Search Console.');
    }

    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $user->google_token = null;
        $user->google_refresh_token = null;
        $user->save();

        return redirect()->route('gsc')->with('success', 'Google Search Console disconnected successfully!');
    }
}
