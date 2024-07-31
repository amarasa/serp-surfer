<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_SearchConsole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Sitemap;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

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
        $user = $request->user();
        $sitemaps = $user->sitemaps()->get();

        return view('profile.google', [
            'user' => $user,
            'sitemaps' => $sitemaps,
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

        // Disconnect GSC
        $user->google_token = null;
        $user->google_refresh_token = null;
        $user->save();

        // Check if sitemaps should be deleted
        if ($request->input('delete_sitemaps') == '1') {
            Sitemap::where('user_id', $user->id)->delete();
        }

        return redirect()->route('gsc')->with('success', 'Google Search Console disconnected successfully!');
    }


    public function syncSitemaps()
    {
        $user = Auth::user();
        $this->client->setAccessToken($user->google_token);

        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $newToken = $this->client->getAccessToken();
            $user->google_token = $newToken['access_token'];
            $user->google_refresh_token = $newToken['refresh_token'];
            $user->save();
        }

        $service = new Google_Service_SearchConsole($this->client);
        $sites = $service->sites->listSites();

        foreach ($sites->getSiteEntry() as $site) {
            $siteURL = $site->getSiteUrl();
            $sitemaps = $service->sitemaps->listSitemaps($siteURL);

            foreach ($sitemaps->getSitemap() as $sitemap) {
                $type = strtolower($sitemap->getType());

                $url = $sitemap->getPath();


                // Store the sitemap
                Sitemap::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'url' => $url,
                    ],
                    [
                        'is_index' => $type === '',
                    ]
                );
            }
        }

        return redirect()->route('gsc')->with('success', 'Sitemaps Synced!');
    }


    public function resyncSitemaps()
    {
        $user = Auth::user();
        Sitemap::where('user_id', $user->id)->delete();

        return $this->syncSitemaps();
    }
}
