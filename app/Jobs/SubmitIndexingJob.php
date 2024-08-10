<?php

namespace App\Jobs;

use App\Models\IndexQueue;
use App\Models\User;
use Google_Client;
use Google_Service_Indexing;
use Google_Service_Indexing_UrlNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitIndexingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Fetch URLs from the index_queue table

        Log::info("WORKING: SubmitIndexingJob");

        $queuedUrls = IndexQueue::whereNull('requested_index_date')
            ->orWhere('requested_index_date', '<=', now()->subDays(7))
            ->get();

        foreach ($queuedUrls as $queuedUrl) {
            $sitemap = $queuedUrl->sitemap;

            if ($sitemap) {
                $user = $sitemap->users->first(); // Get the associated user

                if ($user) {
                    try {
                        // Submit URL to Google Search Console
                        $this->submitToGSC($queuedUrl->url, $user);

                        // Update the requested_index_date and increment submission_count
                        $queuedUrl->update([
                            'requested_index_date' => now(),
                            'submission_count' => $queuedUrl->submission_count + 1,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to submit URL for indexing: {$queuedUrl->url}. Error: {$e->getMessage()}");
                    }
                } else {
                    Log::error("No user associated with the sitemap ID: {$queuedUrl->sitemap_id}");
                }
            } else {
                Log::error("No sitemap found for ID: {$queuedUrl->sitemap_id}");
            }
        }
    }

    protected function submitToGSC(string $url, User $user)
    {
        $client = new Google_Client();
        $client->setApplicationName(env('GOOGLE_APPLICATION_NAME'));
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');

        // Retrieve tokens from the user model
        $accessToken = json_decode($user->google_token, true);
        $refreshToken = $user->google_refresh_token;

        // Set the access token on the client
        $client->setAccessToken($accessToken);

        // Check if the access token is expired and refresh it if necessary
        if ($client->isAccessTokenExpired()) {
            if ($refreshToken) {
                $client->fetchAccessTokenWithRefreshToken($refreshToken);
                $newAccessToken = $client->getAccessToken();

                // Update the user's access token in the database
                $user->update(['google_token' => json_encode($newAccessToken)]);
            } else {
                Log::error("User does not have a valid refresh token.");
                return;
            }
        }

        // Create the Indexing API service
        $service = new Google_Service_Indexing($client);

        // Create the URL notification object
        $postBody = new Google_Service_Indexing_UrlNotification();
        $postBody->setType("URL_UPDATED");
        $postBody->setUrl($url);

        try {
            $service->urlNotifications->publish($postBody);
            Log::info("Successfully submitted URL: {$url} for indexing.");
        } catch (\Exception $e) {
            Log::error("Failed to submit URL: {$url} for indexing. Error: {$e->getMessage()}");
        }
    }
}
