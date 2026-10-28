<?php

use App\Jobs\NotifySubscribersJob;
use App\Models\Article;

test('notify subscribers job is initialized with correct article', function () {
    $article = new Article(['title' => 'Subscribers News']);
    $article->id = 123;

    $job = new NotifySubscribersJob($article);

    expect($job->article->id)->toBe(123)
        ->and($job->article->title)->toBe('Subscribers News');
});
