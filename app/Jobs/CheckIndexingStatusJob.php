<?php

namespace App\Jobs;

use App\Models\IndexQueue;
use App\Models\SitemapUrl;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CheckIndexingStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("WORKING: CheckIndexingStatusJob");

        // Fetch entries from IndexQueue that need to be checked
        $indexQueueItems = IndexQueue::where(function ($query) {
            $query->where('requested_index_date', '<=', now()->subHours(24))
                ->whereNull('last_scan_date')
                ->orWhere(function ($query) {
                    $query->where('last_scan_date', '<=', now()->subHours(24));
                });
        })->get();

        // Loop through each item
        foreach ($indexQueueItems as $item) {
            $url = $item->url;
            $sitemapId = $item->sitemap_id;

            Log::info("Checking indexing status for URL: {$url}");

            try {
                $client = new Client();

                // Google search query to check indexing
                $googleSearchUrl = "https://www.google.com/search?q=site:" . urlencode($url);
                $response = $client->get($googleSearchUrl, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    ],
                ]);

                $crawler = new Crawler($response->getBody()->getContents());
                $isIndexed = $crawler->filter('#search .g')->count() > 0;

                if ($isIndexed) {
                    // Update the sitemap URL with the indexing status
                    SitemapUrl::where('page_url', $url)->update([
                        'index_status' => true,
                    ]);

                    // Remove the item from the IndexQueue
                    $item->delete();

                    // Placeholder for email notification logic
                    // TODO: Trigger email notification here for successful indexing

                    Log::info("URL is indexed and removed from queue: {$url}");
                } else {
                    // Update the last_scan_date if not indexed
                    $item->update([
                        'last_scan_date' => now(),
                    ]);

                    Log::info("URL not indexed, updated last_scan_date: {$url}");
                }
            } catch (RequestException $e) {
                Log::error("Error processing URL: {$url}. Error: {$e->getMessage()}");
            }
        }
    }
}
