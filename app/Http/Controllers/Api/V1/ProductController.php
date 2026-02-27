<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Tag(name="Products", description="Управление товарами")
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Список товаров",
     *     tags={"Products"},
     *     @OA\Parameter(name="category", in="query", description="Фильтр по категории", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", description="Поиск по названию или SKU", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Элементов на странице (по умолчанию 15)", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Номер страницы", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category', 'search', 'per_page']);
        $cacheKey = 'products:' . md5(json_encode($filters) . ':page:' . $request->input('page', 1));

        $queryBuilder = function () use ($request) {
            $query = Product::query();

            if ($category = $request->input('category')) {
                $query->byCategory($category);
            }

            if ($search = $request->input('search')) {
                $query->search($search);
            }

            return $query->orderBy('name')
                ->paginate($request->integer('per_page', 15));
        };

        $store = Cache::getStore();
        $paginator = $store instanceof \Illuminate\Cache\TaggableStore
            ? Cache::tags(['products'])->remember($cacheKey, 300, $queryBuilder)
            : Cache::remember($cacheKey, 300, $queryBuilder);

        return ProductResource::collection($paginator);
    }
}
