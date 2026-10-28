<?php

use App\Models\User;
use App\Models\Article;
use App\Models\Category;
use App\Repositories\ArticleRepositoryInterface;
use App\Repositories\Eloquent\ArticleRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    app()->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
});

test('unauthenticated user can see articles index page and receives 200 if public', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/v1/articles');

    $response->assertStatus(200);
});

test('authenticated user can see articles index page and receives 200', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/articles');

    $response->assertStatus(200);
});

test('only writer can create an article - reader receives 403', function () {
    /** @var \Tests\TestCase $this */
    $reader = User::factory()->create(['role' => 'reader']);
    $category = Category::factory()->create();

    $response = $this->actingAs($reader, 'sanctum')->postJson('/api/v1/articles', [
        'title'       => 'Some New Article Title',
        'content'     => str_repeat('This is a valid long content for the article.', 3),
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);

    $response->assertStatus(403);
});

test('writer tries to create an article without title or content - receives 422 with validation errors', function () {
    /** @var \Tests\TestCase $this */
    Gate::before(fn() => true);
    $writer = User::factory()->create(['role' => 'writer']);

    $response = $this->actingAs($writer, 'sanctum')->postJson('/api/v1/articles', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['title', 'content', 'category_id', 'status']);
});

test('when a writer creates an article successfully, it is saved properly in the database', function () {
    /** @var \Tests\TestCase $this */
    Gate::before(fn() => true);

    $writer = User::factory()->create(['role' => 'writer']);
    $category = Category::factory()->create();

    $response = $this->actingAs($writer, 'sanctum')->postJson('/api/v1/articles', [
        'title'       => 'Valid Article Title 2026',
        'content'     => str_repeat('This is the main content of the article. ', 3),
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('articles', [
        'title'       => 'Valid Article Title 2026',
        'category_id' => $category->id,
        'user_id'     => $writer->id,
    ]);
});

test('only admin can delete an article - and it must be Soft Deleted', function () {
    Gate::before(fn() => true);

    $admin = User::factory()->create(['role' => 'admin']);
    $writer = User::factory()->create(['role' => 'writer']);
    $category = Category::factory()->create();

    $article = Article::factory()->create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
        'status' => 'published',
    ]);

    /** @var \Tests\TestCase $this */
    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/v1/articles/{$article->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('articles', ['id' => $article->id]);
});
