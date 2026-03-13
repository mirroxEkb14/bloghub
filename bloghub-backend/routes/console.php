<?php

use App\Services\NotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:expired-subscriptions', function () {
    $service = app(NotificationService::class);
    $count = $service->processExpiredSubscriptions();
    $this->info("Created {$count} subscription expired notification(s).");
})->purpose('Create notifications for expired subscriptions');

Schedule::command('notifications:expired-subscriptions')->daily();
