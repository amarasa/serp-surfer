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


        $indexQueueItems = IndexQueue::where(function ($query) {
            $query->whereNull('requested_index_date')
                ->orWhere('requested_index_date', '<=', now()->subDays(7));
        })->get();

        foreach ($indexQueueItems as $item) {
            $user = $item->sitemap->users->first(); // Assuming the first user is the one associated with the sitemap

            if ($user) {
                try {
                    // Only submit to GSC if the token is valid
                    if ($this->isValidToken($user->google_token)) {
                        $this->submitToGSC($item->url, $user);

                        // Update only if submission to GSC was successful
                        $item->update([
                            'requested_index_date' => now(),
                            'submission_count' => $item->submission_count + 1,
                        ]);

                        Log::info("URL submitted for indexing: {$item->url}");
                    } else {
                        Log::error("Invalid token for user ID: {$user->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to submit URL: {$item->url} for indexing. Error: {$e->getMessage()}");
                }
            } else {
                Log::error("User not found for sitemap ID: {$item->sitemap_id}");
            }
        }
    }

    protected function isValidToken($token)
    {
        // Assuming the token is a plain string, no JSON parsing needed
        if (empty($token)) {
            return false;
        }

        // Additional token validation logic can be added here if necessary
        return true;
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

        // Check if the token is expired and refresh if necessary
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $user->google_refresh_token;
            Log::info("Refreshing token for user ID: {$user->id}");

            $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $newToken = $client->getAccessToken();
            $user->google_token = $newToken;
            $user->save();
        }

        $client->addScope('https://www.googleapis.com/auth/indexing');

        // Log the scopes
        $scopes = $client->getScopes();
        Log::info("Scopes for Google Client: " . implode(', ', $scopes));


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
