<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessQueuedUrl;

Schedule::job(new ProcessQueuedUrl)->everyThirtySeconds();
