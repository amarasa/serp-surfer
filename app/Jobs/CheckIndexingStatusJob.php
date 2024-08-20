<?php

namespace App\Jobs;

use App\Mail\IndexingSuccessNotification;
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
use Illuminate\Support\Facades\Mail;
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

        $indexQueueItems = IndexQueue::where(function ($query) {
            $query->where('requested_index_date', '<=', now()->subHours(24))
                ->whereNull('last_scan_date')
                ->orWhere(function ($query) {
                    $query->where('last_scan_date', '<=', now()->subHours(24));
                });
        })->get();

        foreach ($indexQueueItems as $item) {
            $url = $item->url;
            $sitemapId = $item->sitemap_id;
            $submissionCount = $item->submission_count;
            $user = $item->sitemap->users->first(); // Assuming the first user is associated with the sitemap

            Log::info("Checking indexing status for URL: {$url}");

            try {
                $client = new Client();

                $googleSearchUrl = "https://www.google.com/search?q=site:" . urlencode($url);
                $response = $client->get($googleSearchUrl, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    ],
                ]);

                $crawler = new Crawler($response->getBody()->getContents());
                $isIndexed = $crawler->filter('#search .g')->count() > 0;

                if ($isIndexed) {
                    SitemapUrl::where('page_url', $url)->update([
                        'index_status' => true,
                    ]);

                    // Send email notification before deleting the queue item
                    if ($user && $user->email) {
                        Mail::to($user->email)->send(new IndexingSuccessNotification($url, $submissionCount));
                    }

                    $item->delete();

                    Log::info("URL is indexed and removed from queue: {$url}");
                } else {
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
