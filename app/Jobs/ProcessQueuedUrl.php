<?php

namespace App\Jobs;

use App\Models\QueuedUrl;
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

class ProcessQueuedUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Fetch the oldest URL from the queue
            $queuedUrl = QueuedUrl::orderBy('id')->first();

            if (!$queuedUrl) {
                Log::info("Queue is empty. Waiting for new URLs...");
                return;
            }

            $url = $queuedUrl->url;
            $sitemapId = $queuedUrl->sitemap_id;

            Log::info("Processing URL: {$url}");

            try {
                $client = new Client();

                // Check if the URL is indexed by Google
                $googleSearchUrl = "https://www.google.com/search?q=site:" . urlencode($url);
                $response = $client->get($googleSearchUrl, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    ],
                ]);

                $crawler = new Crawler($response->getBody()->getContents());
                $isIndexed = $crawler->filter('#search .g')->count() > 0;

                // Fetch the page title
                $pageTitle = 'No title';
                try {
                    $pageResponse = $client->get($url, [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                        ],
                    ]);
                    $pageCrawler = new Crawler($pageResponse->getBody()->getContents());
                    $pageTitle = $pageCrawler->filter('title')->count() > 0 ? $pageCrawler->filter('title')->text() : 'No title';
                } catch (RequestException $e) {
                    Log::error("Error fetching page title: {$e->getMessage()}");
                }

                // Insert or update the sitemap URL details
                SitemapUrl::updateOrCreate(
                    ['page_url' => $url],
                    [
                        'sitemap_id' => $sitemapId,
                        'page_title' => $pageTitle,
                        'index_status' => $isIndexed,
                    ]
                );

                // Remove the processed URL from the queue
                $queuedUrl->delete();

                Log::info("Processed and removed URL from queue: {$url}");
            } catch (RequestException $e) {
                Log::error("Error processing URL: {$e->getMessage()}");
            }
        } catch (\Exception $e) {
            Log::error("Error processing queue: {$e->getMessage()}");
        }
    }
}
