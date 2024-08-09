<?php

namespace App\Jobs;

use App\Models\Sitemap;
use App\Models\SitemapUrl;
use App\Models\QueuedUrl;
use App\Models\UrlList;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AutoScanSitemapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info("WORKING: AutoScanSiteMapsJob");

        $sitemaps = Sitemap::where('auto_scan', true)->get();

        foreach ($sitemaps as $sitemap) {
            Log::info("Scanning sitemap: " . $sitemap->url);
            $this->scanSitemap($sitemap->url);
        }
    }

    protected function scanSitemap($sitemapUrl)
    {
        $sitemap = Sitemap::where('url', $sitemapUrl)->first();

        if (!$sitemap) {
            Log::error("No matching Sitemap found for URL: {$sitemapUrl}");
            return;
        }

        Log::info("Fetching URLs from Sitemap: {$sitemapUrl}");
        $urls = $this->fetchUrlsFromSitemap($sitemapUrl);

        foreach ($urls as $url) {
            $existsInQueued = QueuedUrl::where('url', $url)->exists();
            $existsInSitemap = SitemapUrl::where('page_url', $url)->exists();

            if (!$existsInQueued && !$existsInSitemap) {
                Log::info("Queuing URL: {$url} with Sitemap ID: {$sitemap->id}");
                QueuedUrl::create([
                    'sitemap_id' => $sitemap->id,
                    'url' => $url
                ]);

                Log::info("Adding URL to url_list: {$url} with Sitemap ID: {$sitemap->id}");
                UrlList::create([
                    'url' => $url,
                    'sitemap_id' => $sitemap->id,
                    'status' => 'queued',
                    'last_seen' => now(),
                ]);

                Log::info("New URL added to queue: " . $url);
            } else {
                UrlList::updateOrCreate(
                    ['url' => $url],
                    [
                        'last_seen' => now(),
                        'sitemap_id' => $sitemap->id,
                    ]
                );
            }
        }
    }

    protected function fetchUrlsFromSitemap($sitemapUrl)
    {
        $urls = [];

        try {
            $xml = simplexml_load_file($sitemapUrl);

            if ($xml !== false) {
                if (isset($xml->sitemap)) {
                    foreach ($xml->sitemap as $nestedSitemapElement) {
                        $nestedSitemapUrl = (string)$nestedSitemapElement->loc;
                        $urls = array_merge($urls, $this->fetchUrlsFromSitemap($nestedSitemapUrl));
                    }
                } elseif (isset($xml->url)) {
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
