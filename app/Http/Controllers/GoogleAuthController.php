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
use GuzzleHttp\Client;
use Google\Service\Iam as GoogleIam;
use Google\Service\Iam\Resource\ProjectsServiceAccounts;
use Google\Service\Iam\ServiceAccount;
use Google\Service\Iam\Policy;
use Google\Service\Iam\Binding;

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

                // Assuming `createAndAssignServiceWorker` updates the service worker address in the database
                if ($sitemapModel->serviceWorker) {
                    $sitemapModel->update([
                        'service_worker_address' => $sitemapModel->serviceWorker->address,
                        'service_worker_online' => true, // Assuming the service worker is online after creation
                    ]);
                }
            }
        }

        return redirect()->route('gsc')->with('success', 'Sitemaps Synced!');
    }

    public function resyncSitemaps()
    {
        $user = Auth::user();

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
        // Step 1: Initialize the Google Client
        $client = new GoogleClient();
        $client->setApplicationName(config('google.application_name'));
        $client->useApplicationDefaultCredentials();
        $client->addScope(GoogleIam::CLOUD_PLATFORM);

        // Step 2: Initialize the IAM service
        $iamService = new GoogleIam($client);
        $projectId = config('google.project_id');

        // Step 3: Create a new service account
        $serviceAccountName = 'serp-surfer-indexing-' . uniqid();
        $serviceAccountEmail = $serviceAccountName . '@' . $projectId . '.iam.gserviceaccount.com';

        $serviceAccount = new ServiceAccount();
        $serviceAccount->setAccountId($serviceAccountName);
        $serviceAccount->setDisplayName('Serp Surfer Indexing');

        $createdServiceAccount = $iamService->projects_serviceAccounts->create(
            'projects/' . $projectId,
            $serviceAccount
        );

        // Step 4: Assign the Owner Role
        $policy = new Policy();
        $binding = new Binding();
        $binding->setRole('roles/owner');
        $binding->setMembers(['serviceAccount:' . $serviceAccountEmail]);
        $policy->setBindings([$binding]);

        $iamService->projects_serviceAccounts->setIamPolicy(
            'projects/' . $projectId . '/serviceAccounts/' . $serviceAccountEmail,
            $policy
        );

        // Step 5: Generate the JSON key
        $key = $iamService->projects_serviceAccounts_keys->create(
            'projects/' . $projectId . '/serviceAccounts/' . $serviceAccountEmail
        );

        $jsonKey = json_encode($key['privateKeyData']); // Store this in your database

        // Step 6: Store in the service_workers table
        $worker = ServiceWorker::create([
            'address' => $serviceAccountEmail,
            'json_key' => $jsonKey,
            'used' => 0,
        ]);

        return $worker;
    }
}
