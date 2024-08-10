<?php

namespace App\Jobs;

use App\Models\IndexQueue;
use App\Models\User;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Indexing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitIndexingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch URLs from the index_queue table that meet the criteria
        $now = Carbon::now();

        // Rule 1: If requested_index_date is null
        $urlsToSubmit = IndexQueue::whereNull('requested_index_date')
            ->orWhere(function ($query) use ($now) {
                // Rule 2: If requested_index_date was 7 days ago or more
                $query->where('requested_index_date', '<=', $now->subDays(7));
            })
            ->get();

        foreach ($urlsToSubmit as $url) {
            try {
                $user = $url->sitemap->user; // Assuming the User model is related to Sitemap
                $this->submitToGSC($url->url, $user);

                // Update the requested_index_date and increment submission_count
                $url->requested_index_date = $now;
                $url->submission_count++;
                $url->save();

                Log::info('Submitted URL for indexing: ' . $url->url);
            } catch (\Exception $e) {
                Log::error('Failed to submit URL: ' . $url->url . ' Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Submit the URL to Google Search Console for indexing.
     *
     * @param string $url
     * @param \App\Models\User $user
     * @return void
     */
    protected function submitToGSC(string $url, User $user)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken($user->google_token); // Assuming you store the OAuth token in the user model

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($user->google_refresh_token);
            $user->google_token = $client->getAccessToken();
            $user->save();
        }

        $service = new Google_Service_Indexing($client);

        $postBody = new \Google_Service_Indexing_UrlNotification();
        $postBody->setType("URL_UPDATED"); // You can use URL_UPDATED or URL_DELETED depending on your need
        $postBody->setUrl($url);

        try {
            $service->urlNotifications->publish($postBody);
            Log::info("Successfully submitted $url to Google Search Console.");
        } catch (\Exception $e) {
            Log::error("Failed to submit $url to Google Search Console: " . $e->getMessage());
            throw $e; // Re-throw to handle it in the main try-catch
        }
    }
}
