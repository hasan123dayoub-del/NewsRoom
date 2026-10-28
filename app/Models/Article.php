<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\ArticleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ArticleObserver::class)]
class Article extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "title",
        "content",
        "category_id",
        "status",
        "published_at",
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
