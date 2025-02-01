<?php

namespace App\Domains\ApiResponse\Resources;


use App\Domains\Cart\Resources\VariationsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderItemResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): CustomerOrderItemResource
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
        $relations = self::$relations;

        $data = [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "name" => $this->name,
            "picture" => $this->picture,
            "shipping_cost" => $this->shipping_cost,
            "total_amount" => $this->total_amount,
            "status" => $this->status,
        ];

        $relations = self::$relations;

        if (in_array('product', $relations)) {
            $data["sku"] = $this->product ? $this->product->sku : null;
            $data["product_uuid"] = $this->product ? $this->product->uuid : null;
            $data["thumbnail_img"] = $this->product ? asset($this->product->thumbnail_img) : null;
        }

        if (in_array('variations', $relations)) {
            $data["variations"] = VariationsResource::collection($this->variations);
            $data['item_total'] = $this->variationSubTotal();
        }

        return $data;
    }

    public function variationSubTotal()
    {
        return $this->variations->map(function ($q) {
            $data = $q;
            $data['variation_total'] = ($q->quantity * $q->sale_price);
            return $data;
        })->sum('variation_total');
    }
}
