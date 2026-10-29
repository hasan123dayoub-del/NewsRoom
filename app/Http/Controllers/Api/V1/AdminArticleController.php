<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationSenderInterface;

class AdminArticleController extends Controller
{
    protected NotificationSenderInterface $notificationSender;
    public function __construct(NotificationSenderInterface $notificationSender)
    {
        $this->notificationSender = $notificationSender;
    }

    public function review()
    {
        $this->notificationSender->send('the article has been reviewed by admin');

        return response()->json(['message' => 'Admin notified in the database (DatabaseNotificationSender)']);
    }
}
