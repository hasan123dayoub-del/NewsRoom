<?php

use Tests\TestCase;
use function Pest\Laravel\getJson;
use App\Models\Article;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('article index returns data and meta structure', function () {
    app()->bind(\App\Repositories\ArticleRepositoryInterface::class, \App\Repositories\Eloquent\ArticleRepository::class);

    Article::factory()->count(3)->create(['status' => 'published']);

    $response = getJson('/api/v1/articles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'author_name', 'published_at']
            ],
            'meta' => ['current_page', 'from', 'last_page', 'total'],
            'links'
        ]);
});


test('article show does not expose sensitive data', function () {
    app()->bind(\App\Repositories\ArticleRepositoryInterface::class, \App\Repositories\Eloquent\ArticleRepository::class);

    $article = Article::factory()->create(['status' => 'published']);

    $user = User::factory()->create();

    /** @var \Tests\TestCase $this */

    $response = $this->actingAs($user)
        ->getJson("/api/v1/articles/{$article->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'title', 'content', 'author_name', 'published_at']])
        ->assertJsonMissing(['password', 'remember_token']);
});
