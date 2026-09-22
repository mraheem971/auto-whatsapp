<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('campaign:run-single {id}', function ($id) {
    $this->info("Starting automatic background campaign execution for ID: {$id}");
    \App\Services\CampaignDispatcherService::executeCampaign((int) $id);
    $this->info("Completed execution for campaign ID: {$id}");
})->purpose('Execute a WhatsApp marketing campaign in the background');

Artisan::command('campaign:dispatch-all', function () {
    $running = \App\Models\Campaign::where('status', 'running')->get();
    foreach ($running as $c) {
        $this->info("Dispatching running campaign ID: {$c->id}");
        \App\Services\CampaignDispatcherService::launchBackgroundProcess($c->id);
    }
})->purpose('Dispatch all active running campaigns in the background');
