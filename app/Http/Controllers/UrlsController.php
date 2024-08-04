<?php

namespace App\Http\Controllers;

use App\Models\SitemapUrl;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UrlsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch domains by filtering sitemaps associated with the user
        $sitemaps = $user->sitemaps;
        $domains = SitemapUrl::whereIn('sitemap_id', $sitemaps->pluck('id'))
            ->distinct()
            ->get()
            ->map(function ($url) {
                return parse_url($url->page_url, PHP_URL_HOST);
            })
            ->unique()
            ->values()
            ->all();

        $selectedDomain = $request->query('domain');

        $urls = collect();
        if ($selectedDomain) {
            $urls = SitemapUrl::where('page_url', 'like', "%{$selectedDomain}%")
                ->whereIn('sitemap_id', $sitemaps->pluck('id'))
                ->paginate(12);
        }

        return view('dashboard', compact('domains', 'urls', 'selectedDomain'));
    }
}
