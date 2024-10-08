<?php

namespace App\Http\Controllers;

use App\Models\UrlList;
use App\Models\Sitemap;
use App\Models\SitemapUrl;
use App\Models\QueuedUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{

    public function queueSitemap(Sitemap $sitemap)
    {
        Log::info('queueSitemap method called for sitemap ID: ' . $sitemap->id);

        try {
            // Start a transaction to ensure atomicity
            \DB::beginTransaction();

            // Delete all existing queued URLs for the sitemap to avoid duplicates
            QueuedUrl::where('sitemap_id', $sitemap->id)->delete();

            // Delete all existing processed URLs for the sitemap to avoid duplicates
            SitemapUrl::where('sitemap_id', $sitemap->id)->delete();

            // Call a method to recursively fetch all URLs from the sitemap
            $urls = $this->fetchAllUrls($sitemap);

            // Loop through each URL and add it to the queue
            foreach ($urls as $url) {
                // Delete matching entries from the url_list table
                UrlList::where('url', $url)->delete();

                // Add to the queued_urls table
                QueuedUrl::create([
                    'sitemap_id' => $sitemap->id,
                    'url' => $url,
                ]);

                // Add to the url_list table with status 'queued'
                UrlList::create([
                    'url' => $url,
                    'sitemap_id' => $sitemap->id,
                    'status' => 'queued',
                    'last_seen' => now(),
                ]);
            }

            // Commit the transaction
            \DB::commit();

            // Flash success message
            return redirect()->back()->with('success', 'Sitemap URLs have been successfully queued.');
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            \DB::rollBack();

            // Log the error for debugging purposes
            Log::error('Error queuing sitemap URLs: ' . $e->getMessage());

            // Flash error message
            return redirect()->back()->with('error', 'An error occurred while queuing the sitemap URLs.');
        }
    }

    protected function fetchAllUrls(Sitemap $sitemap)
    {
        $urls = [];

        // Check if the sitemap is an index sitemap
        if ($sitemap->is_index) {
            // Fetch the main sitemap's XML
            $mainXml = simplexml_load_file($sitemap->url);

            if ($mainXml !== false && isset($mainXml->sitemap)) {
                // Iterate through each nested sitemap
                foreach ($mainXml->sitemap as $nestedSitemapElement) {
                    $nestedSitemapUrl = (string)$nestedSitemapElement->loc;
                    // Parse URLs from the nested sitemap
                    $urls = array_merge($urls, $this->parseSitemap($nestedSitemapUrl));
                }
            } else {
                \Log::error("Failed to load main sitemap or no nested sitemaps found: {$sitemap->url}");
            }
        } else {
            // If it's not an index sitemap, directly parse the URLs
            $urls = $this->parseSitemap($sitemap->url);
        }

        return $urls;
    }

    protected function parseSitemap($sitemapUrl)
    {
        $urls = [];

        try {
            // Load and parse the XML sitemap
            $xml = simplexml_load_file($sitemapUrl);

            if ($xml !== false) {
                // Check if it's an index sitemap
                if (isset($xml->sitemap)) {
                    foreach ($xml->sitemap as $sitemapElement) {
                        $nestedSitemapUrl = (string)$sitemapElement->loc;
                        // Recursively parse nested sitemaps
                        $urls = array_merge($urls, $this->parseSitemap($nestedSitemapUrl));
                    }
                } elseif (isset($xml->url)) {
                    // Regular sitemap with URLs
                    foreach ($xml->url as $urlElement) {
                        $urls[] = (string)$urlElement->loc;
                    }
                } else {
                    \Log::error("Error parsing sitemap: No URL or Sitemap elements found or failed to load XML for URL: {$sitemapUrl}");
                }
            } else {
                \Log::error("Error parsing sitemap: Failed to load XML for URL: {$sitemapUrl}");
            }
        } catch (\Exception $e) {
            \Log::error("Error parsing sitemap: {$e->getMessage()} for URL: {$sitemapUrl}");
        }

        return $urls;
    }

    public function toggleAutoScan(Request $request, Sitemap $sitemap)
    {
        $validated = $request->validate([
            'auto_scan' => 'required|boolean',
        ]);

        $sitemap->auto_scan = $validated['auto_scan'];
        $sitemap->save();

        return response()->json(['success' => true, 'auto_scan' => $sitemap->auto_scan]);
    }
}
