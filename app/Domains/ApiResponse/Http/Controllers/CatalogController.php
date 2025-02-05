<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\CatalogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{

    public CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }


    public function productDetails($sku): JsonResponse
    {
        $product = $this->catalogService->product($sku);
        return $this->success($product, 'product details');
    }

    public function relatedProducts($item_sku): JsonResponse
    {
        $products = $this->catalogService->related_products($item_sku);
        return $this->success($products, 'product load successfully');
    }

    public function getSectionProducts(): JsonResponse
    {
        $products = $this->catalogService->section_products();
        return $this->success($products, 'product load successfully');
    }

    public function getFilteredProducts(): JsonResponse
    {
        $products = $this->catalogService->filtered_products();
        return $this->success($products, 'data load successfully');
    }

    public function getSubjects(): JsonResponse
    {
        $data = $this->catalogService->bookSubjects();
        return $this->success(['items' => $data], 'data load successfully');
    }

    public function getWriters(): JsonResponse
    {
        $data = $this->catalogService->bookWriters();
        return $this->success(['items' => $data], 'data load successfully');
    }

    public function getPublishers(): JsonResponse
    {
        $data = $this->catalogService->bookPublisher();
        return $this->success(['items' => $data], 'data load successfully');
    }

    public function getAttributeItems($type): JsonResponse
    {
        $data = $this->catalogService->bookAttributes($type);
        return $this->success($data, 'data load successfully');
    }


//    =============== below are not implemented ==============

    public function storeDetails($slug): JsonResponse
    {
        $product = $this->catalogService->store($slug);
        return $this->success($product, 'fetch store details');
    }


//     ================ below are not implemented =============


    public function featuredProducts()
    {
        $products = $this->catalogService->featured_products();
        return response($products);
    }

    public function lovingProducts(): JsonResponse
    {
        $products = $this->catalogService->loving_products();
        return $this->success($products, 'product load successfully');
    }

    public function newArrivedProducts(): JsonResponse
    {
        $products = $this->catalogService->new_products();
        return $this->success($products, 'product load successfully');
    }

    public function recentViewProducts(): JsonResponse
    {
        $products = $this->catalogService->recent_view_products();
        return $this->success($products, 'product load successfully');
    }

    public function searchSuggestion(): JsonResponse
    {
        $suggestion = $this->catalogService->search_suggestions();
        return $this->success($suggestion, 'suggestion load successfully');
    }

    public function getSearchResult(): JsonResponse
    {
        $products = $this->catalogService->search_result();
        return $this->success($products, 'product load successfully');
    }

    public function categoryProducts($slug): JsonResponse
    {
        $products = $this->catalogService->category_products($slug);
        return $this->success($products, 'product load successfully');
    }

    public function uploadProductGalleryUpload(): JsonResponse
    {
        $products = $this->catalogService->uploadProductGalleryUpload();
        return response()->json($products);
    }
}
