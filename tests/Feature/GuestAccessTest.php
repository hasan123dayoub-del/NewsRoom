<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

test('guests can read articles but cannot comment or upload attachments', function () {
    $writer = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create([
        'user_id' => $writer->id,
        'category_id' => Category::factory()->create()->id,
        'status' => 'published',
    ]);

    /** @var TestCase $this */
    $this->getJson('/api/v1/articles')->assertOk();
    $this->getJson("/api/v2/articles/{$article->id}")->assertOk();

    $this->postJson("/api/v1/articles/{$article->id}/comments", ['content' => 'Hello'])
        ->assertUnauthorized();
    $this->postJson("/api/v1/articles/{$article->id}/attachments", [
        'file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
    ])->assertUnauthorized();

    $this->assertDatabaseCount('comments', 0);
    $this->assertDatabaseCount('attachments', 0);
});
