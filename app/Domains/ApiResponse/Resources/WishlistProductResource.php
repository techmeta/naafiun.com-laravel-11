<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class WishlistProductResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): WishlistProductResource
    {
        self::$relations = $data;
        return parent::make($resource);
    }

    public static function collection($resource, $data = []): AnonymousResourceCollection
    {
        self::$relations = $data;
        return parent::collection($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
//        $relations = self::$relations;
        $product = $this->product;

        if (!$product) {
            return [];
        }

        return [
            "id" => $product->id,
            "uuid" => $product->uuid,
            "name" => $product->name,
            "slug" => Str::slug($product->name),
            "sku" => $product->sku,
            "sale_price" => $product->sale_price,
            "discount_price" => $product->discount_price,
            "sale_unit" => $product->sale_unit,
            "alert_qty" => $product->alert_qty,
            "available" => $product->available,
            "thumbnail_img" => $product->thumbnail_img ? asset($product->thumbnail_img) : '',
            "rating" => $product->rating,
        ];

    }

}
