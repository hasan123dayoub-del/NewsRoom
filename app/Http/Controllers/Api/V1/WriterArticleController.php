<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationSenderInterface;

class WriterArticleController extends Controller
{
    protected NotificationSenderInterface $notificationSender;

    public function __construct(NotificationSenderInterface $notificationSender)
    {
        $this->notificationSender = $notificationSender;
    }

    public function publish()
    {
        $this->notificationSender->send('the article has been published by the writer');

        return response()->json(['message' => 'Writer notified by email (EmailNotificationSender)']);
    }
}
