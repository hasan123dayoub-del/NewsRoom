<?php

namespace App\Http\Requests;

use App\Rules\ValidStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Models\Article;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Article::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge([
                'title' => Str::title(preg_replace('/\s+/', ' ', trim($this->title)))
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'title'   => ['required', 'string', 'min:10', 'unique:articles,title'],
            'content' => ['required', 'string', 'min:100'],
            'tags'    => ['nullable', 'array'],
            'tags.*'  => ['exists:tags,id'],
            'status'  => ['required', new ValidStatus()],
        ];
    }
}
