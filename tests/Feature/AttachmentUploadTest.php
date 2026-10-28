<?php

use App\Models\{User, Article};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('writer can upload attachment', function () {
    Storage::fake('public');
    $writer = User::factory()->create(['role' => 'writer']);
    $article = Article::factory()->create(['user_id' => $writer->id]);
    $file = UploadedFile::fake()->create('doc.pdf', 500);

    /** @var \Tests\TestCase $this */

    $this->actingAs($writer)
        ->postJson("/api/v1/articles/{$article->id}/attachments", ['file' => $file])
        ->assertStatus(201);

    $this->assertTrue(Storage::disk('public')->exists('attachments/' . $file->hashName()));
    $this->assertDatabaseHas('attachments', [
        'attachable_id'   => $article->id,
        'attachable_type' => Article::class,
    ]);
});
