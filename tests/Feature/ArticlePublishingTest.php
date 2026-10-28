<?php

use App\Models\{Article, User};
use App\Jobs\ProcessArticlePublishing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\Eloquent\ArticleRepository;

uses(RefreshDatabase::class);

test('publishing an article triggers mail and job', function () {
    app()->bind(ArticleRepositoryInterface::class, ArticleRepository::class);

    \Illuminate\Support\Facades\Event::fake();

    $writer = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create([
        'user_id' => $writer->id,
        'status' => 'draft'
    ]);
    /** @var \Tests\TestCase $this */

    $response = $this->actingAs($writer)
        ->putJson("/api/v1/articles/{$article->id}", [
            'status'  => 'published',
            'title'   => 'Updated Title That Is Long Enough',
            'content' => str_repeat('Updated content for the article that is definitely more than 100 characters long.', 2)
        ]);

    $response->assertStatus(200);

    \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\ArticlePublished::class);
});
