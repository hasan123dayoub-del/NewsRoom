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
        // The @example values pre-fill "create article" in the API docs with a request that works for the demo writer.
        return [
            /** @example 2 */
            'category_id' => 'required|exists:categories,id',
            /** @example Our New Code Review Checklist */
            'title'   => ['required', 'string', 'min:10', 'unique:articles,title'],
            /** @example Starting this sprint, every pull request is reviewed against a short checklist: tests, naming, error handling and documentation. It keeps reviews fast and consistent across teams. */
            'content' => ['required', 'string', 'min:100'],
            /** @example [1, 4] */
            'tags'    => ['nullable', 'array'],
            'tags.*'  => ['exists:tags,id'],
            /** @example draft */
            'status'  => ['required', new ValidStatus()],
        ];
    }
}
