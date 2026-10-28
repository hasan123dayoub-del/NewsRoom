<?php

use App\Mail\ArticlePublishedMail;
use App\Models\Article;
use App\Models\User;

test('mail has correct subject and recipient', function () {
    $writer = new User(['email' => 'writer@example.com']);
    $article = new Article(['title' => 'My First Article']);
    $article->setRelation('user', $writer);

    $mail = new ArticlePublishedMail($article);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Your article has been published!');

    expect($mail->article->title)->toBe('My First Article');
});
