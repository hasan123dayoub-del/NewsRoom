<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via(object $notifiable): array
    {
        if ($notifiable->role === 'admin') {
            return ['database'];
        }

        return ['mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'article_id' => $this->comment->article_id,
            'body'       => 'New Comment: ' . substr($this->comment->body, 0, 50),
            'user_name'  => $this->comment->user->name,
        ];
    }
}
