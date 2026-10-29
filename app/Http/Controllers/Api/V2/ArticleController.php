<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\V2\ArticleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Repositories\ArticleRepositoryInterface;
use App\Services\ArticleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ArticleController extends Controller
{
    use AuthorizesRequests;
    protected $articleRepository;
    protected $articleService;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        ArticleService $articleService
    ) {
        $this->articleRepository = $articleRepository;
        $this->articleService = $articleService;
    }

    public function index(): AnonymousResourceCollection
    {
        $articles = $this->articleRepository->getPublishedArticlesV2();

        return ArticleResource::collection($articles);
    }

    public function show(Article $article): JsonResource
    {
        $this->authorize('view', $article);

        $detailedArticle = $this->articleRepository->findWithDetails($article->id);

        return new ArticleResource($detailedArticle);
    }

    // Writes use the same service and rules as v1; only the response shape (V2 resource) differs.
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = $this->articleService->createArticle($request->user(), $request->validated());

        return response()->json([
            'message' => 'Article created successfully',
            'article' => new ArticleResource($article->loadCount('comments')),
        ], 201);
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $updatedArticle = $this->articleService->updateArticle($article, $request->validated());

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => new ArticleResource($updatedArticle->loadCount('comments')),
        ]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }
}
