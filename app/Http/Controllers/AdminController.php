<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sitemap;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Models\SitemapUrl;
use App\Models\ServiceWorker;
use App\Models\IndexQueue;
use Illuminate\Support\Facades\Log;

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



    public function urls(Request $request)
    {
        // Fetch distinct domains from all sitemap URLs
        $domains = SitemapUrl::selectRaw('DISTINCT(SUBSTRING_INDEX(SUBSTRING_INDEX(page_url, "/", 3), "/", -1)) AS domain')
            ->pluck('domain')
            ->unique()
            ->values()
            ->all();

        // Get the selected domain from the request or use the first one by default
        $selectedDomain = $request->query('domain', $domains ? $domains[0] : null);

        $urls = collect();
        if ($selectedDomain) {
            $urls = SitemapUrl::where('page_url', 'like', "%{$selectedDomain}%")
                ->with(['urlList' => function ($query) {
                    $query->select('url', 'last_seen'); // Select the relevant columns
                }])
                ->paginate(12);

            // Check each URL if it is in the index queue
            foreach ($urls as $url) {
                $inQueue = IndexQueue::where('url', $url->page_url)->exists();

                // Add a custom attribute to the URL model to indicate if it's in the queue
                $url->inQueue = $inQueue;
            }
        }

        return view('admin.index', compact('domains', 'urls', 'selectedDomain'));
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

    public function GoogleServiceWorkers()
    {
        $workers = ServiceWorker::orderBy('address', 'asc')->paginate(12);

        return view('admin.index', compact('workers'));
    }

    public function AddGoogleServiceWorkers(Request $request)
    {
        // Validate that a file has been uploaded
        $request->validate([
            'json_file' => 'required|file|mimes:json',
        ]);

        // Read the uploaded JSON file
        $jsonContent = file_get_contents($request->file('json_file')->getRealPath());

        // Decode the JSON content to extract the client_email
        $jsonDecoded = json_decode($jsonContent, true);

        // Check if the JSON is valid and contains the required key
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonDecoded['client_email'])) {
            // Extract the client_email and store it in the address column
            $clientEmail = $jsonDecoded['client_email'];

            // Store the worker information in the database
            ServiceWorker::create([
                'address' => $clientEmail,
                'json_key' => $jsonContent,
                'used' => 0,
            ]);

            Log::info("Service worker {$clientEmail} added successfully.");
        } else {
            return redirect()->route('service.workers')->with('error', 'Invalid JSON file or missing client_email.');
        }

        // Fetch the updated list of workers and return to the view
        $workers = ServiceWorker::orderBy('address', 'asc')->paginate(12);
        return view('admin.index', compact('workers'))->with('success', 'Service Worker added successfully.');
    }
}
