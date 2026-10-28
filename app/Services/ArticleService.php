<?php

namespace App\Services;

use App\Repositories\ArticleRepositoryInterface;
use App\Models\User;
use App\Models\Article;

class ArticleService
{
    protected $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function createArticle(User $user, array $data): Article
    {
        $article = new Article($data);

        $article->user_id = $user->id;

        $article->save();

        if (isset($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        return $article;
    }
    public function updateArticle(Article $article, array $data): Article
    {
        $oldStatus = $article->status;

        $updatedArticle = $this->articleRepository->update($article->id, $data);

        if (isset($data['tags'])) {
            $updatedArticle->tags()->sync($data['tags']);
        }

        if ($oldStatus !== 'published' && $updatedArticle->status === 'published') {
            \App\Events\ArticlePublished::dispatch($updatedArticle);
        }

        return $updatedArticle;
    }
}
