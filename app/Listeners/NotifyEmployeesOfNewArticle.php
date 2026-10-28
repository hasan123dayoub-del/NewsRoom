<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Models\User;
use App\Jobs\SendNotificationToChunkJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyEmployeesOfNewArticle implements ShouldQueue
{
    use InteractsWithQueue;

    public $connection = 'redis';
    public $queue = 'notifications';
    public function handle(ArticlePublished $event): void
    {
        if ($this->attempts() > 3) {
            $this->delete();
            return;
        }

        Log::info("Asynchronous Queue: Starting chunked notification dispatch for approved article: '{$event->article->title}'");

        User::select('id')->chunk(100, function ($users) use ($event) {

            $userIds = $users->pluck('id')->toArray();

            SendNotificationToChunkJob::dispatch($userIds, $event->article);
        });

        Log::info("Asynchronous Queue: All notification chunks have been successfully pushed to Redis!");
    }

    public function failed(ArticlePublished $event, \Throwable $exception): void
    {
        Log::error("The dispatched listener failed for article ID [{$event->article->id}]. Error: {$exception->getMessage()}");
    }
}
