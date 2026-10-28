<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        "user_id",
        "file_path",
        "file_name",
        "mime_type",
        "attachable_id",
        "attachable_type",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attachable()
    {
        return $this->morphTo();
    }
}
