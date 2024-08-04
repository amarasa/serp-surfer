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

        // Fetch all distinct domains for the logged-in user
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

        // Default to the first domain if no domain is selected
        $selectedDomain = $request->query('domain', $domains[0] ?? null);

        // If a domain is selected or default exists, fetch URLs
        $urls = collect();
        if ($selectedDomain) {
            $urls = SitemapUrl::where('page_url', 'like', "%{$selectedDomain}%")
                ->paginate(12);
        }

        return view('dashboard', compact('domains', 'urls', 'selectedDomain'));
    }
}
