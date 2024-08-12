<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sitemap;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Models\SitemapUrl;
use App\Models\ServiceWorker;

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
        $workers = ServiceWorker::orderBy('name', 'asc')->paginate(12);

        return view('admin.index', compact('workers'));
    }
}
