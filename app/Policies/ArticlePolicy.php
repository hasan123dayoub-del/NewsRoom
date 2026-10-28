<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->role === 'writer';
    }

    public function view(?User $user = null, Article $article): bool
    {
        if ($article->status === 'published') {
            return true;
        }

        return $user && ($user->id === $article->user_id || $user->role === 'admin');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->role !== 'writer' || $user->id !== $article->user_id) {
            dump("Auth Failed: User Role: {$user->role}, User ID: {$user->id}, Article User ID: {$article->user_id}");
        }

        return $user->role === 'writer' && $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->role === 'writer' && $user->id === $article->user_id;
    }
}
