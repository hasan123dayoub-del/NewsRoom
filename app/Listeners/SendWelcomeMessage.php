<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWelcomeMessage implements ShouldQueue
{
    use InteractsWithQueue;
    public $queue = 'default';

    public function handle(UserRegistered $event): void
    {
        $profile = $event->user->profile;

        Log::info("Asynchronous Queue: Welcome email triggered for TechNova employee: {$profile->first_name} ({$event->user->email})");
    }
    public function failed(UserRegistered $event, Throwable $exception): void
    {
        Log::error("Queue Failed: Welcome email could not be sent to User ID: {$event->user->id}. Reason: {$exception->getMessage()}");
    }
}
