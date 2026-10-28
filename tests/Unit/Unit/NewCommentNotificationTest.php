<?php

use App\Notifications\NewCommentNotification;
use App\Models\User;
use App\Models\Comment;

test('new comment notification via correct channels', function () {
    $writer = new User(['role' => 'writer']);
    $admin = new User(['role' => 'admin']);
    $comment = new Comment();

    $notification = new NewCommentNotification($comment);

    expect($notification->via($writer))->toContain('mail');

    expect($notification->via($admin))->toContain('database');
});
