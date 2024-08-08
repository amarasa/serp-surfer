<?php

namespace App\Jobs;

use App\Models\UrlList;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class Janitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch and delete URLs with null sitemap_id
        $nullSitemapUrls = UrlList::whereNull('sitemap_id')->get();

        foreach ($nullSitemapUrls as $urlEntry) {
            $url = $urlEntry->url;
            Log::info("Removing URL with null sitemap_id: {$url}");
            $urlEntry->delete();
        }

        Log::info("Completed janitor task: removed URLs with null sitemap_id.");
    }
}
