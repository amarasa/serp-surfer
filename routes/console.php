<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessQueuedUrl;
use App\Jobs\AutoScanSitemapsJob;

Schedule::job(new ProcessQueuedUrl)->everyMinute();
Schedule::job(new AutoScanSitemapsJob)->everyMinute();
