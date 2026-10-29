<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New comment on your article')
            ->line($this->comment->user->name.' commented on your article:')
            ->line('"'.Str::limit($this->comment->content, 200).'"');
    }

    public function toArray(object $notifiable): array
    {
        // Comments are polymorphic: the article id is stored in commentable_id and the text in content.
        return [
            'article_id' => $this->comment->commentable_id,
            'body' => 'New Comment: '.Str::limit($this->comment->content, 50),
            'user_name' => $this->comment->user->name,
        ];
    }
}
