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
        $urls = $defaultDomain ? SitemapUrl::where('page_url', 'like', "%{$defaultDomain}%")->paginate(12) : [];

        return view('dashboard', compact('domains', 'urls'));
    }

    public function getUrlsByDomain(Request $request)
    {
        $domain = $request->query('domain');
        $urls = SitemapUrl::where('page_url', 'like', "%{$domain}%")->paginate(12);
        return response()->json(['urls' => $urls]);
    }
}
