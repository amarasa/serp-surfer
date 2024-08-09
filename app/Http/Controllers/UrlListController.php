<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SitemapUrl;

class UrlListController extends Controller
{
    public function index(Request $request)
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

        return view('admin.url-list', compact('domains', 'urls', 'selectedDomain'));
    }
}
