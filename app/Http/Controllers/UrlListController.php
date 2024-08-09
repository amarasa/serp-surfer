<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SitemapUrl;

class UrlListController extends Controller
{
    public function index(Request $request)
    {
        // Get distinct domains for the dropdown
        $domains = SitemapUrl::distinct()->pluck('domain_column'); // Adjust 'domain_column' to the actual column name in your table

        // Get the selected domain from the request, default to the first domain if none is selected
        $selectedDomain = $request->input('domain', $domains->first());

        // Fetch URLs related to the selected domain and paginate
        $urls = SitemapUrl::where('domain_column', $selectedDomain)
            ->with('urlList') // Ensure the relationship exists and is properly defined
            ->paginate(12);

        // Pass the data to the view
        return view('admin.url-list', compact('domains', 'selectedDomain', 'urls'));
    }
}
