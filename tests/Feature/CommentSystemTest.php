<?php

use App\Models\User;
use App\Models\Article;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Notification;

test('reader can add a comment and author gets notified', function () {
    Notification::fake();

    $writer = User::factory()->create(['role' => 'writer']);
    $reader = User::factory()->create(['role' => 'reader']);

    $category = \App\Models\Category::factory()->create();

    $article = Article::factory()->create([
        'user_id'     => $writer->id,
        'category_id' => $category->id,
        'status'      => 'published'
    ]);

    /** @var \Tests\TestCase $this */
    $response = $this->actingAs($reader, 'sanctum')
        ->postJson("/api/v1/articles/{$article->id}/comments", [
            'content' => 'This is a test comment'
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('comments', [
        'commentable_id'   => $article->id,
        'commentable_type' => Article::class,
        'user_id'          => $reader->id,
        'content'          => 'This is a test comment'
    ]);

    Notification::assertSentTo($writer, NewCommentNotification::class);
});

test('reader does not get notification for their own comment', function () {
    Notification::fake();

    $reader = User::factory()->create(['role' => 'reader']);
    $article = Article::factory()->create(['user_id' => $reader->id]);

    /** @var \Tests\TestCase $this */

    $this->actingAs($reader, 'sanctum')
        ->postJson("/api/v1/articles/{$article->id}/comments", [
            'content' => 'My own comment'
        ]);

    Notification::assertNotSentTo($reader, NewCommentNotification::class);
});
