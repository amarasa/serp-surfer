<?php

namespace App\Jobs;

use App\Models\Sitemap;
use App\Models\SitemapUrl; // Add this import
use App\Models\QueuedUrl; // Ensure this is imported as well
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AutoScanSitemapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch all sitemaps with auto_scan enabled
        $sitemaps = Sitemap::where('auto_scan', true)->get();

        foreach ($sitemaps as $sitemap) {
            Log::info("Scanning sitemap: " . $sitemap->url);

            $this->scanSitemap($sitemap->url);
        }
    }

    /**
     * Scan a sitemap and add new URLs to the queued_urls table.
     *
     * @param string $sitemapUrl
     * @return void
     */
    protected function scanSitemap($sitemapUrl)
    {
        $urls = $this->fetchUrlsFromSitemap($sitemapUrl);

        foreach ($urls as $url) {
            // Check if the URL is already in queued_urls or sitemap_urls
            $existsInQueued = QueuedUrl::where('url', $url)->exists();
            $existsInSitemap = SitemapUrl::where('page_url', $url)->exists();

            if (!$existsInQueued && !$existsInSitemap) {
                // Add the new URL to the queued_urls table
                QueuedUrl::create(['url' => $url]);

                // Log the new URL added
                Log::info("New URL added to queue: " . $url);
            }
        }
    }

    /**
     * Fetch URLs from a sitemap, including handling nested sitemaps.
     *
     * @param string $sitemapUrl
     * @return array
     */
    protected function fetchUrlsFromSitemap($sitemapUrl)
    {
        $urls = [];

        try {
            $xml = simplexml_load_file($sitemapUrl);

            if ($xml !== false) {
                if (isset($xml->sitemap)) {
                    // Nested sitemap
                    foreach ($xml->sitemap as $nestedSitemapElement) {
                        $nestedSitemapUrl = (string)$nestedSitemapElement->loc;
                        $urls = array_merge($urls, $this->fetchUrlsFromSitemap($nestedSitemapUrl));
                    }
                } elseif (isset($xml->url)) {
                    // Regular sitemap
                    foreach ($xml->url as $urlElement) {
                        $urls[] = (string)$urlElement->loc;
                    }
                }
            } else {
                Log::error("Failed to load XML for URL: {$sitemapUrl}");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching URLs from sitemap: {$e->getMessage()} for URL: {$sitemapUrl}");
        }

        return $urls;
    }
}
