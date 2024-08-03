<?php

namespace App\Http\Controllers;

use App\Models\SitemapUrl;

use Illuminate\Http\Request;

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
        $urls = collect(); // No data initially

        return view('dashboard', compact('domains', 'urls'));
    }

    public function getUrlsByDomain(Request $request)
    {
        $domain = $request->query('domain');

        // Fetch URLs based on the selected domain
        $urls = SitemapUrl::where('page_url', 'like', "%{$domain}%")
            ->paginate(12); // Assuming pagination with 12 items per page

        // Return the URLs and pagination information
        return response()->json([
            'urls' => $urls->items(),
            'pagination' => $urls->links()->render(), // Return rendered pagination HTML
        ]);
    }
}
