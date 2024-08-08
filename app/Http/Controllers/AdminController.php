<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sitemap;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $ayoo = 'Welcome to the Admin Dashboard';
        return view('admin.index', compact('ayoo'));
    }

    public function users(Request $request)
    {
        $query = $request->input('query');

        // Fetch users with pagination and search functionality
        $users = User::when($query, function ($queryBuilder) use ($query) {
            return $queryBuilder->where('name', 'like', "%{$query}%");
        })->paginate(12);

        return view('admin.index', compact('users', 'query'));
    }


    public function sitemaps()
    {
        $sitemaps = Sitemap::with('users')->paginate(12);
        return view('admin.index', compact('sitemaps'));
    }



    public function urls()
    {
        $ayoo = 'URL List';
        // Add your logic here to get the URL list
        return view('admin.index', compact('ayoo'));
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        $users = User::where('name', 'like', "%{$query}%")->get();
        return response()->json($users);
    }

    public function toggleSuspend(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {
            $user->suspended = !$user->suspended;
            $user->save();

            $status = $user->suspended ? 'User suspended successfully.' : 'User unsuspended successfully.';

            return response()->json(['success' => $status]);
        }

        return response()->json(['success' => false]);
    }

    public function resetPassword(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {
            $user->force_password_reset = true;
            $user->save();

            // Send password reset email
            $status = Password::sendResetLink(['email' => $user->email]);

            if ($status == Password::RESET_LINK_SENT) {
                return response()->json(['success' => 'Password reset email sent successfully.']);
            }

            return response()->json(['success' => false, 'message' => __($status)]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteUser(User $user)
    {
        try {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete user.']);
        }
    }

    public function toggleAutoScan(Request $request)
    {
        $sitemap = Sitemap::find($request->sitemap_id);

        if ($sitemap) {
            $sitemap->auto_scan = !$sitemap->auto_scan;
            $sitemap->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function searchSitemaps(Request $request)
    {
        $query = $request->input('query');
        $sitemaps = Sitemap::where('url', 'like', "%{$query}%")
            ->with(['users', 'sitemapUrls']) // Ensure sitemapUrls is included
            ->get();

        return response()->json($sitemaps);
    }


    public function processSitemap(Request $request)
    {
        $sitemapId = $request->input('sitemap_id');
        $sitemap = Sitemap::find($sitemapId);

        if (!$sitemap) {
            return response()->json(['success' => false, 'message' => 'Sitemap not found']);
        }

        try {
            $this->client->setAccessToken(Auth::user()->google_token);

            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken(Auth::user()->google_refresh_token);
                $newToken = $this->client->getAccessToken();
                Auth::user()->google_token = $newToken['access_token'];
                Auth::user()->google_refresh_token = $newToken['refresh_token'];
                Auth::user()->save();
            }

            $service = new Google_Service_SearchConsole($this->client);
            $sitemaps = $service->sitemaps->listSitemaps($sitemap->url);

            foreach ($sitemaps->getSitemap() as $sitemapData) {
                $type = strtolower($sitemapData->getType());
                $url = $sitemapData->getPath();

                // Find or create the sitemap
                $sitemapModel = Sitemap::firstOrCreate(
                    [
                        'url' => $url,
                    ],
                    [
                        'is_index' => $type === 'index',
                    ]
                );

                // Queue URLs for processing
                $this->queueSitemapUrls($sitemapModel);
            }

            return response()->json(['success' => true, 'message' => 'Sitemap processing initiated']);
        } catch (\Exception $e) {
            Log::error("Error processing sitemap: {$e->getMessage()}");
            return response()->json(['success' => false, 'message' => 'Error processing sitemap']);
        }
    }

    protected function queueSitemapUrls(Sitemap $sitemap)
    {
        $urls = $this->fetchUrlsFromSitemap($sitemap->url);

        foreach ($urls as $url) {
            // Check if the URL is already in queued_urls or sitemap_urls
            $existsInQueued = QueuedUrl::where('url', $url)->exists();
            $existsInSitemap = SitemapUrl::where('page_url', $url)->exists();

            if (!$existsInQueued && !$existsInSitemap) {
                // Add the new URL to the queued_urls table with the associated sitemap_id
                QueuedUrl::create([
                    'sitemap_id' => $sitemap->id,
                    'url' => $url
                ]);

                // Add the new URL to the url_list table
                UrlList::create([
                    'url' => $url,
                    'sitemap_id' => $sitemap->id,
                    'status' => 'queued',
                    'last_seen' => now(),
                ]);

                // Log the new URL added
                Log::info("New URL added to queue: " . $url);
            } else {
                // Update the last seen timestamp in the url_list table
                UrlList::updateOrCreate(
                    ['url' => $url],
                    [
                        'last_seen' => now(),
                        'sitemap_id' => $sitemap->id,
                    ]
                );
            }
        }
    }

    protected function fetchUrlsFromSitemap($sitemapUrl)
    {
        $urls = [];

        try {
            $xml = simplexml_load_file($sitemapUrl);

            if ($xml !== false) {
                if (isset($xml->sitemap)) {
                    // Nested sitemap
                    foreach ($xml->sitemap as $nestedSitemapElement) {
                        $nestedSitemapUrl = (string)$nestedSitemapElement->loc;
                        $urls = array_merge($urls, $this->fetchUrlsFromSitemap($nestedSitemapUrl));
                    }
                } elseif (isset($xml->url)) {
                    // Regular sitemap
                    foreach ($xml->url as $urlElement) {
                        $urls[] = (string)$urlElement->loc;
                    }
                }
            } else {
                Log::error("Failed to load XML for URL: {$sitemapUrl}");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching URLs from sitemap: {$e->getMessage()} for URL: {$sitemapUrl}");
        }

        return $urls;
    }
}
