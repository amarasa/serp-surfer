<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ServiceWorker;

class AssignServiceWorkers extends Command
{
    protected $signature = 'assign:service-workers';
    protected $description = 'Assigns service workers to users who do not have one';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $usersWithoutServiceWorker = User::whereNull('service_worker_id')->get();

        if ($usersWithoutServiceWorker->isEmpty()) {
            $this->info('All users already have a service worker assigned.');
            return;
        }

        foreach ($usersWithoutServiceWorker as $user) {
            $user->assignServiceWorker();
            $this->info("Assigned service worker to user ID {$user->id}");
        }

        $this->info('Service workers assigned to all users without one.');
    }
}
