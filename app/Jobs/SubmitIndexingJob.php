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
        // Retrieve all URLs from the index_queue table that need to be processed
        $urlsToProcess = IndexQueue::whereNull('requested_index_date')
            ->orWhere('requested_index_date', '<=', Carbon::now()->subDays(7))
            ->get();

        foreach ($urlsToProcess as $indexQueueItem) {
            // Retrieve the user associated with the sitemap
            $user = $indexQueueItem->sitemap->users->first(); // Adjust as needed to get the correct user

            if ($user) {
                try {
                    // Submit the URL for indexing
                    $this->submitToGSC($indexQueueItem->url, $user);

                    // Update the requested_index_date and increment submission_count
                    $indexQueueItem->update([
                        'requested_index_date' => Carbon::now(),
                        'submission_count' => $indexQueueItem->submission_count + 1,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to submit URL for indexing: {$indexQueueItem->url}", ['error' => $e->getMessage()]);
                }
            } else {
                Log::error("No user associated with sitemap for URL: {$indexQueueItem->url}");
            }
        }
    }

    /**
     * Submit a URL to Google Search Console for indexing.
     *
     * @param string $url
     * @param User $user
     * @throws \Exception
     */
    protected function submitToGSC(string $url, User $user)
    {
        if (!$user->google_token || !$user->google_refresh_token) {
            throw new \Exception("GSC credentials not found or invalid for user ID: {$user->id}");
        }

        $client = new Google_Client();
        $client->setAccessToken([
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => 3600,
            'created' => time(),
        ]);

        // Refresh the token if it's expired
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($user->google_refresh_token);

            // Save the new access token
            $user->google_token = $client->getAccessToken()['access_token'];
            $user->save();
        }

        $service = new Google_Service_Indexing($client);

        $postBody = new \Google_Service_Indexing_UrlNotification();
        $postBody->setType("URL_UPDATED");
        $postBody->setUrl($url);

        try {
            $service->urlNotifications->publish($postBody);
        } catch (\Exception $e) {
            throw new \Exception("Failed to submit URL: {$url} for indexing. Error: {$e->getMessage()}");
        }
    }
}
