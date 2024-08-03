<?php

namespace App\Http\Controllers;

use App\Models\SitemapUrl;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UrlsController extends Controller
{
    public function getDomains()
    {
        $user = auth()->user();
        $domains = SitemapUrl::whereHas('sitemap', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->distinct()
            ->get()
            ->map(function ($url) {
                return parse_url($url->page_url, PHP_URL_HOST);
            })
            ->unique()
            ->values()
            ->all();

        // Default to the first domain's URLs if available
        $defaultDomain = $domains[0] ?? null;
        $urls = new LengthAwarePaginator([], 0, 12); // Empty paginator


        return view('dashboard', compact('domains', 'urls'));
    }

    public function getUrlsByDomain(Request $request)
    {
        $domain = $request->query('domain');

        // Fetch URLs based on the selected domain
        $urls = SitemapUrl::where('page_url', 'like', "%{$domain}%")
            ->paginate(12); // Assuming pagination with 12 items per page

        // Check if the request expects JSON (typically for AJAX)
        if ($request->ajax()) {
            return response()->json([
                'urls' => $urls->items(),
                'pagination' => (string) $urls->links() // Ensure this returns HTML
            ]);
        }

        // For non-AJAX requests, return the full view
        return view('dashboard', compact('urls', 'domain'));
    }
}
