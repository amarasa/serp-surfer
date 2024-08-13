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
use App\Models\ServiceWorker;

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

    public function submitToGSC($url)
    {
        // Retrieve the least-used service worker with its JSON key from the database
        $worker = ServiceWorker::orderBy('used', 'asc')
            ->orderBy('address', 'asc')
            ->first();

        if (!$worker) {
            Log::error("No available service worker found.");
            return;
        }

        // Decode the JSON key from the database
        $credentials = json_decode($worker->json_key, true);

        if (!$credentials || !isset($credentials['client_email'])) {
            Log::error("Invalid service worker credentials.");
            return;
        }

        // Initialize Google Client with the JSON key
        $client = new Google_Client();
        $client->setAuthConfig($credentials); // Use the decoded JSON credentials array
        $client->addScope('https://www.googleapis.com/auth/indexing');

        // Log the service account email being used
        Log::info('Using service account: ' . $credentials['client_email']);

        // Initialize the Indexing API service
        $service = new Google_Service_Indexing($client);

        // Prepare the URL notification
        $content = new Google_Service_Indexing_UrlNotification();
        $content->setType('URL_UPDATED');
        $content->setUrl($url);

        // Attempt to submit the URL
        try {
            $service->urlNotifications->publish($content);
            Log::info("URL submitted for indexing: $url");

            // Only update the database when the submission is successful
            IndexQueue::where('url', $url)->update([
                'requested_index_date' => now(),
                'submission_count' => \DB::raw('submission_count + 1'),
            ]);

            // Increment the used count for the service worker
            $worker->increment('used');
        } catch (\Exception $e) {
            Log::error("Failed to submit URL: $url for indexing. Error: " . $e->getMessage());
        }
    }
}
