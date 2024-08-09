<?php

namespace App\Http\Controllers;

use App\Models\SitemapUrl;
use App\Models\UrlList;
use App\Models\IndexQueue;
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
        $sitemapId = null;  // Default to null in case there are no URLs
        if ($selectedDomain) {
            $urls = SitemapUrl::where('page_url', 'like', "%{$selectedDomain}%")
                ->whereIn('sitemap_id', $sitemaps->pluck('id'))
                ->with(['urlList' => function ($query) {
                    $query->select('url', 'last_seen'); // Select the relevant columns
                }])
                ->paginate(12);

            // Get the sitemap_id from the first URL in the collection if URLs are found
            if ($urls->isNotEmpty()) {
                $sitemapId = $urls->first()->sitemap_id;

                // Check each URL if it is in the index queue
                foreach ($urls as $url) {
                    $inQueue = IndexQueue::where('url', $url->page_url)->exists();

                    // Add a custom attribute to the URL model to indicate if it's in the queue
                    $url->inQueue = $inQueue;
                }
            }
        }

        dd($urls);
        return view('dashboard', compact('domains', 'urls', 'selectedDomain', 'sitemapId'));
    }
}
