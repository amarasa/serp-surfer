<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessQueuedUrl;
use App\Jobs\AutoScanSitemapsJob;
use App\Jobs\RemoveOldUrlsJob;

Schedule::job(new ProcessQueuedUrl)->everyMinute();
Schedule::job(new AutoScanSitemapsJob)->everyMinute();
Schedule::job(new RemoveOldUrlsJob)->everyMinute();
