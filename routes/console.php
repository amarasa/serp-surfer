<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessQueuedUrl;
use App\Jobs\AutoScanSitemapsJob;
use App\Jobs\RemoveOldUrlsJob;
use App\Jobs\SubmitIndexingJob;
use App\Jobs\CheckIndexingStatusJob;
use App\Jobs\Janitor;

/* 
    This job is responsible for taking URLs in the queue and scraping google to get the data we need. 
    We keep this at 1 minute, because it'll scrap Google once per minute at  most.
*/

Schedule::job(new ProcessQueuedUrl)->everyMinute();

/* 
    This job is responsible for seeing what sitemaps have been enabled to scan for a fresh set of URLs.
    Any new URLs will be added to the URLs list table, with the current timestamp
    Any old URLS that are in our urls list table and also in the sitemap will have the timestamp column updated with the current timestamp
    Any URLs that were in our urls list table, but no longer in the sitemap will not have the timestamp updated and once the 72 hour marker hits
        then the "RemoveOldJobs" worker will delete the URL.

    This job should run once a day at 8:00am EST
*/
Schedule::job(new AutoScanSitemapsJob)->dailyAt('8:00')->timezone('America/Detroit');

/* 
    This job is responsible for looking through the URLS list table ,specifically the column with the last_seen timestamp
    and any time stamp than is 72 hours or greater, that entry will be deleted

    This should run once every 6 hours
*/
Schedule::job(new RemoveOldUrlsJob)->everySixHours();

/* 
    This job is responsible for simple cleanups:

        1. If the sitemap_id column in the URLs table is blank for whatever reason, then simply delete the row.

    This job runs once a minute
*/
Schedule::job(new Janitor)->everyMinute();

/* 
    This job is responsible for submitted URLs to GSC for indexing. This should run once every 6 hours
*/
Schedule::job(new SubmitIndexingJob)->everyMinute();

/* 
    This job is responsible for scanning URLs in the index_queue to see if they've been indexed, so we can update the status and trigger an email.
    This should be set to every 1 hour after testing
*/
//Schedule::job(new CheckIndexingStatusJob)->everyMinute();
