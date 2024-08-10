<?php

namespace App\Jobs;

use App\Models\IndexQueue;
use App\Models\User;
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

    public function handle()
    {
        Log::info("WORKING: SubmitIndexingJob");

        // Fetch the URLs from the indexing queue that need to be processed
        $indexQueueItems = IndexQueue::where(function ($query) {
            $query->whereNull('requested_index_date')
                ->orWhere('requested_index_date', '<=', now()->subDays(7));
        })->get();

        foreach ($indexQueueItems as $item) {
            $user = $item->sitemap->users->first(); // Assuming the first user is the one associated with the sitemap

            if ($user) {
                try {
                    $this->submitToGSC($item->url, $user);

                    // Update the requested_index_date to the current timestamp
                    $item->update([
                        'requested_index_date' => now(),
                        'submission_count' => $item->submission_count + 1,
                    ]);

                    Log::info("URL submitted for indexing: {$item->url}");
                } catch (\Exception $e) {
                    Log::error("Failed to submit URL: {$item->url} for indexing. Error: {$e->getMessage()}");
                }
            } else {
                Log::error("User not found for sitemap ID: {$item->sitemap_id}");
            }
        }
    }

    protected function submitToGSC($url, User $user)
    {
        $token = $user->google_token;

        Log::info("User's Google token: " . $token);

        $client = new Google_Client();
        $client->setApplicationName(env('GOOGLE_APPLICATION_NAME'));
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');
        $client->setAccessToken($token);

        // Debug: Check if the token is valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Invalid JSON token for user ID: {$user->id}. Error: " . json_last_error_msg());
            return;
        }

        // Debug: Fetch and log granted scopes
        $grantedScopes = $client->getScopes();
        Log::info("Granted Scopes: " . implode(', ', $grantedScopes));

        // Check if the token is expired, and refresh it if necessary
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $user->google_refresh_token;
            Log::info("Refreshing token for user ID: {$user->id}");

            $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $newToken = $client->getAccessToken();
            $user->google_token = $newToken;
            $user->save();
        }

        // Ensure the necessary scope is granted
        $client->addScope('https://www.googleapis.com/auth/indexing');

        $service = new Google_Service_Indexing($client);

        $notification = new \Google_Service_Indexing_UrlNotification();
        $notification->setUrl($url);
        $notification->setType("URL_UPDATED");

        try {
            $service->urlNotifications->publish($notification);
            Log::info("Successfully submitted URL: {$url} for indexing.");
        } catch (\Exception $e) {
            Log::error("Failed to submit URL: {$url} for indexing. Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
