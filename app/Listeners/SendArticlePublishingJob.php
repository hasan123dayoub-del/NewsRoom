<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Notifications\NewArticleNotification;
use App\Jobs\ProcessArticlePublishing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendArticlePublishingJob
{
    public function handle(ArticlePublished $event): void
    {
        $article = $event->article;

        $article->user->notify(new NewArticleNotification($article));

        ProcessArticlePublishing::dispatch($article);
    }
}
