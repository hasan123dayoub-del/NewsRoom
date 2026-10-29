<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

test('a writer can create, update and delete an article through v2', function () {
    $writer = User::factory()->create(['role' => 'writer']);
    $category = Category::factory()->create();

    /** @var TestCase $this */
    $created = $this->actingAs($writer, 'sanctum')->postJson('/api/v2/articles', [
        'category_id' => $category->id,
        'title' => 'Our New Code Review Checklist',
        'content' => str_repeat('Every pull request is reviewed against a short checklist. ', 4),
        'status' => 'draft',
    ]);

    $created->assertCreated()->assertJsonPath('article.reading_time', '1 min');
    $id = $created->json('article.id');

    $this->actingAs($writer, 'sanctum')
        ->putJson("/api/v2/articles/{$id}", ['title' => 'Our Updated Code Review Checklist'])
        ->assertOk()
        ->assertJsonPath('article.title', 'Our Updated Code Review Checklist');

    $this->actingAs($writer, 'sanctum')->deleteJson("/api/v2/articles/{$id}")->assertOk();
    expect(Article::find($id))->toBeNull();
});
