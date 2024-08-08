<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessQueuedUrl;
use App\Jobs\AutoScanSitemapsJob;
use App\Jobs\RemoveOldUrlsJob;
use App\Jobs\Janitor;

Schedule::job(new ProcessQueuedUrl)->everyMinute();
Schedule::job(new AutoScanSitemapsJob)->everyMinute();
Schedule::job(new RemoveOldUrlsJob)->everyMinute();
Schedule::job(new Janitor)->everyMinute();

// Schedule::job(new AutoScanSitemapsJob)->dailyAt('8:00')->timezone('America/Detroit');
// Schedule::job(new RemoveOldUrlsJob)->everySixHours();
