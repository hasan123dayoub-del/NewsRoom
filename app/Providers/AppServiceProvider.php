<?php

namespace App\Providers;

use App\Http\Controllers\Api\V1\AdminArticleController;
use App\Http\Controllers\Api\V1\WriterArticleController;
use App\Repositories\Eloquent\ArticleRepository;
use App\Repositories\ArticleRepositoryInterface;
use App\Services\NotificationSenderInterface;
use App\Services\DatabaseNotificationSender;
use App\Services\EmailNotificationSender;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Events\ArticlePublished;
use App\Listeners\SendArticlePublishingJob;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);

        $this->app->when(AdminArticleController::class)
            ->needs(NotificationSenderInterface::class)
            ->give(DatabaseNotificationSender::class);

        $this->app->when(WriterArticleController::class)
            ->needs(NotificationSenderInterface::class)
            ->give(EmailNotificationSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            ArticlePublished::class,
            SendArticlePublishingJob::class,
        );
    }
}
