<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = new \App\Models\Comment([
            'content' => $validated['content'],
        ]);

        $comment->user_id = auth()->id();

        $article->comments()->save($comment);

        if ($article->user_id !== auth()->id()) {
            $article->user->notify(new NewCommentNotification($comment));
        }

        return response()->json([
            'message' => 'Comment added successfully',
            'data'    => $comment
        ], 201);
    }
}
