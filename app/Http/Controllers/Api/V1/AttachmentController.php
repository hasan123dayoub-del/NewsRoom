<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        $file = $request->file('file');

        $path = $file->store('attachments', 'public');

        $attachment = $article->attachments()->create([
            'user_id'   => auth()->id(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'data'    => $attachment
        ], 201);
    }
}
