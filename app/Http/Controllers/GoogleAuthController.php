<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_SearchConsole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Sitemap;
use App\Models\IndexQueue;
use App\Models\ServiceWorker;
use Illuminate\Support\Facades\Log;
use Google\Service\Iam;
use Google\Service\Iam\ServiceAccount;
use Google\Service\Iam\CreateServiceAccountRequest;
use Google\Service\Iam\CreateServiceAccountKeyRequest;
use Google\Service\Iam\Resource\ProjectsServiceAccountsKeys;
use GuzzleHttp\Client;
use Google\Client as GoogleClient;

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
        $sitemaps = $user->sitemaps; // Changed to fetch sitemaps via relationship

        return view('profile.google', [
            'user' => $user,
            'sitemaps' => $sitemaps,
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->get('code')) {
            // Set the necessary scopes, including the Google Indexing API scope
            $this->client->setScopes([
                Google_Service_SearchConsole::WEBMASTERS,
                'https://www.googleapis.com/auth/indexing'
            ]);

            // Authenticate and get the access token
            $this->client->authenticate($request->get('code'));
            $token = $this->client->getAccessToken();

            // Ensure we have the access and refresh tokens
            if (isset($token['access_token']) && isset($token['refresh_token'])) {
                $user = Auth::user();
                $user->google_token = $token['access_token'];
                $user->google_refresh_token = $token['refresh_token'];
                $user->save();

                return redirect()->route('gsc')->with('success', 'Google Search Console connected successfully!');
            } else {
                return redirect()->route('gsc')->with('error', 'Failed to retrieve the necessary tokens from Google.');
            }
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

        // Detach all associated sitemaps
        if ($request->input('delete_sitemaps') == '1') {
            $user->sitemaps()->detach(); // Detach instead of delete
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

                // Find or create the sitemap, then attach it to the user
                $sitemapModel = Sitemap::firstOrCreate(
                    [
                        'url' => $url,
                    ],
                    [
                        'is_index' => $type === 'index',
                    ]
                );

                $user->sitemaps()->syncWithoutDetaching($sitemapModel->id);

                // Call the function to create and assign a new service worker
                $this->createAndAssignServiceWorker($sitemapModel);
            }
        }

        return redirect()->route('gsc')->with('success', 'Sitemaps Synced!');
    }

    public function resyncSitemaps(Request $request)
    {
        $user = Auth::user();

        // Check if 'overwrite' is true
        $overwrite = $request->input('overwrite', false); // Default to false if 'overwrite' is not provided

        if ($overwrite) {
            // Get all sitemap IDs associated with the user
            $sitemapIds = $user->sitemaps()->pluck('sitemap_id');

            // Detach user from sitemaps
            $user->sitemaps()->detach();

            // Delete entries in url_list associated with the detached sitemaps
            \App\Models\UrlList::whereIn('sitemap_id', $sitemapIds)->delete();

            // Delete sitemaps that are no longer associated with any user
            Sitemap::whereNotIn('id', function ($query) {
                $query->select('sitemap_id')
                    ->from('sitemap_user');
            })->delete();
        }

        // Continue with syncing sitemaps, regardless of whether 'overwrite' is true or false
        return $this->syncSitemaps();
    }


    public function submitIndexList(Request $request)
    {
        // Step 1: Retrieve the list of URLs submitted from the request
        $submittedUrls = $request->input('urls'); // Assuming 'urls' is an array of URLs

        // Initialize an array to hold the URLs that were successfully added to the index_queue
        $addedUrls = [];

        foreach ($submittedUrls as $url) {
            // Step 2: Check if the URL is already in the index_queue
            $existingEntry = IndexQueue::where('url', $url)->first();

            if (!$existingEntry) {
                // Step 3: Add the URL to the index_queue table
                $indexQueueEntry = IndexQueue::create([
                    'url' => $url,
                    'sitemap_id' => $request->input('sitemap_id'), // Assuming sitemap_id is also provided in the request
                    'submission_count' => 0,
                ]);

                // Add the successfully added URL to the confirmation list
                $addedUrls[] = $url;
            }
        }

        // Step 4: Return the list of submitted URLs to the view
        return view('pages.indexing', ['urls' => $addedUrls]);
    }



    public function createAndAssignServiceWorker($sitemap)
    {
        $client = new Client();

        // Assuming you have set up your Google Client correctly and have a valid access token
        $googleClient = $this->client;
        $googleClient->addScope('https://www.googleapis.com/auth/cloud-platform');

        if ($googleClient->isAccessTokenExpired()) {
            $googleClient->fetchAccessTokenWithRefreshToken($googleClient->getRefreshToken());
        }
        $accessToken = $googleClient->getAccessToken()['access_token'];

        $projectId = config('google.project_id');
        $serviceAccountName = 'serp-surfer-indexing-' . uniqid();

        try {
            // Step 1: Create the service account using REST API
            $createServiceAccountUrl = "https://iam.googleapis.com/v1/projects/{$projectId}/serviceAccounts";
            $createServiceAccountResponse = $client->post($createServiceAccountUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'accountId' => $serviceAccountName,
                    'serviceAccount' => [
                        'displayName' => 'Serp Surfer Indexing Service Account',
                    ],
                ],
            ]);

            $serviceAccountData = json_decode($createServiceAccountResponse->getBody()->getContents(), true);
            $serviceAccountEmail = $serviceAccountData['email'];

            // Step 2: Generate and download the JSON key for the new service account
            $createServiceAccountKeyUrl = "https://iam.googleapis.com/v1/{$serviceAccountData['name']}/keys";
            $createServiceAccountKeyResponse = $client->post($createServiceAccountKeyUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'privateKeyType' => 'TYPE_GOOGLE_CREDENTIALS_FILE',
                ],
            ]);

            $keyData = json_decode($createServiceAccountKeyResponse->getBody()->getContents(), true);
            $privateKeyData = base64_decode($keyData['privateKeyData']);

            // Step 3: Save the JSON key and service account details to the database
            $serviceWorker = ServiceWorker::create([
                'address' => $serviceAccountEmail,
                'json_key' => $privateKeyData,
                'used' => 1,
            ]);

            // Step 4: Attach the service worker to the sitemap
            $sitemap->update([
                'service_worker_address' => $serviceWorker->address,
                'service_worker_online' => false
            ]);

            return $serviceWorker;
        } catch (\Exception $e) {
            Log::error('Failed to create and assign service worker: ' . $e->getMessage());
            return null;
        }
    }
}
