<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Services\ArticleService;
use App\Repositories\ArticleRepositoryInterface;
use App\Models\Article;
use App\Http\Resources\V1\ArticleResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    protected ArticleService $articleService;
    protected ArticleRepositoryInterface $articleRepository;

    public function __construct(
        ArticleService $articleService,
        ArticleRepositoryInterface $articleRepository
    ) {
        $this->articleService = $articleService;
        $this->articleRepository = $articleRepository;
    }
    public function index(): AnonymousResourceCollection
    {
        $articles = $this->articleRepository->getPublishedArticlesV1();

        return ArticleResource::collection($articles);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $article = $this->articleService->createArticle($request->user(), $validatedData);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => new ArticleResource($article)
        ], 201);
    }

    public function show(Article $article): JsonResource
    {
        $this->authorize('view', $article);

        $detailedArticle = $this->articleRepository->findWithDetails($article->id);

        return new ArticleResource($detailedArticle);
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $validatedData = $request->validated();

        $updatedArticle = $this->articleService->updateArticle($article, $validatedData);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => new ArticleResource($updatedArticle)
        ]);
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully'], 200);
    }
}
