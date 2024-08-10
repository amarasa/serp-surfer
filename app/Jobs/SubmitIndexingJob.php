<?php

namespace App\Jobs;

use App\Models\IndexQueue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Indexing;

class SubmitIndexingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        // You can pass any needed parameters here if required.
    }

    public function handle()
    {
        Log::info("WORKING: SubmitIndexingJob");

        // Fetch all URLs from the indexing queue that need to be processed
        $urls = IndexQueue::whereNull('requested_index_date')
            ->orWhere('requested_index_date', '<=', now()->subDays(7))
            ->get();

        foreach ($urls as $url) {
            // Retrieve the sitemap along with its users
            $sitemap = $url->sitemap()->with('users')->first();

            // Ensure there's a sitemap and at least one associated user
            if ($sitemap && $sitemap->users->isNotEmpty()) {
                // Use the first associated user for this example
                $user = $sitemap->users->first();

                try {
                    $this->submitToGSC($url->url, $user);

                    // Update the indexing queue record
                    $url->update([
                        'requested_index_date' => now(),
                        'submission_count' => $url->submission_count + 1,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to submit URL for indexing: {$url->url}", ['error' => $e->getMessage()]);
                }
            } else {
                Log::warning("No user associated with sitemap for URL: {$url->url}");
            }
        }
    }

    protected function submitToGSC(string $url, User $user)
    {
        try {
            // Check if the user has GSC credentials
            if (!$user->gsc_credentials_path || !file_exists($user->gsc_credentials_path)) {
                throw new \Exception("GSC credentials not found or invalid for user ID: {$user->id}");
            }

            // Initialize the Google Client
            $client = new Google_Client();
            $client->setAuthConfig($user->gsc_credentials_path);
            $client->addScope(Google_Service_Indexing::INDEXING);

            // Check if the client is correctly authenticated
            if (!$client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithAssertion();
            }

            $service = new Google_Service_Indexing($client);

            // Prepare the request body
            $postBody = new \Google_Service_Indexing_UrlNotification();
            $postBody->setType("URL_UPDATED");
            $postBody->setUrl($url);

            // Submit to Google Search Console
            $response = $service->urlNotifications->publish($postBody);

            Log::info("Successfully submitted URL to GSC: {$url}", ['response' => $response]);
        } catch (\Exception $e) {
            // Log specific errors to help diagnose the problem
            Log::error("Failed to submit URL for indexing: {$url}", ['error' => $e->getMessage()]);
        }
    }
}
