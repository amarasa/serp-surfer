<?php

namespace App\Http\Controllers;

use App\Models\SitemapUrl;
use App\Models\UrlList;
use App\Models\IndexQueue;
use App\Models\IndexingResult;
use Illuminate\Contracts\View\View;
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

        return view('dashboard', compact('domains', 'urls', 'selectedDomain', 'sitemapId'));
    }

    public function indexHistory(Request $request)
    {

        // Get the logged-in user
        $user = auth()->user();

        // Check if the user has visited the Index History page
        $featureInteractions = $user->feature_interactions ?? [];

        $hasVisitedIndexHistory = isset($featureInteractions['index_history']) && $featureInteractions['index_history'];

        // If the user has not visited the Index History page, mark it as visited
        if (!$hasVisitedIndexHistory) {
            $featureInteractions['index_history'] = true;
            $user->feature_interactions = $featureInteractions;
            $user->save();
        }

        // Get the selected domain from the request, if any
        $selectedDomain = $request->get('domain');

        // Initialize the indexing results as an empty collection by default
        $indexingResults = collect();

        // Retrieve the sitemaps associated with the user
        $sitemaps = $user->sitemaps();

        // Fetch indexing results only if a domain is selected
        if ($selectedDomain) {
            // Filter sitemaps by the selected domain
            $sitemaps = $sitemaps->where('url', 'like', "%$selectedDomain%");
            $sitemaps = $sitemaps->get();

            // Fetch all indexing results associated with the user's filtered sitemaps
            $indexingResults = IndexingResult::whereIn('sitemap_id', $sitemaps->pluck('id'))
                ->orderBy('index_date', 'desc')
                ->paginate(12);
        }

        // Extract domain names from sitemap URLs and remove duplicates
        $domains = $user->sitemaps->pluck('url')->map(function ($url) {
            return parse_url($url, PHP_URL_HOST);
        })->unique();

        // Pass the indexing results and domains to the view
        return view('profile.history', compact('indexingResults', 'domains', 'selectedDomain'));
    }
}
