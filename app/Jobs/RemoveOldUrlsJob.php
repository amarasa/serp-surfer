<?php

namespace App\Jobs;

use App\Models\UrlList;
use App\Models\QueuedUrl;
use App\Models\SitemapUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RemoveOldUrlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the current time minus 72 hours
        $threshold = Carbon::now()->subHours(72);

        // Fetch URLs that haven't been seen in the last 72 hours
        $oldUrls = UrlList::where('last_seen', '<', $threshold)->get();

        foreach ($oldUrls as $urlEntry) {
            $url = $urlEntry->url;

            Log::info("Removing old URL: {$url}");

            // Delete from queued_urls
            QueuedUrl::where('url', $url)->delete();

            // Delete from sitemap_urls
            SitemapUrl::where('page_url', $url)->delete();

            // Delete from url_list
            $urlEntry->delete();
        }

        Log::info("Completed removing old URLs.");
    }
}
