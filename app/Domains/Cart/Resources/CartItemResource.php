<?php

namespace App\Domains\Cart\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): CartItemResource
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
        $data = [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "product_id" => $this->product_id,
            "name" => $this->name,
            "picture" => $this->picture ? asset($this->picture) : '',
            "product_link" => $this->product_link,
            "is_cart" => $this->is_cart,
            "is_selected" => $this->is_selected,
            "is_popup_shown" => (int)$this->is_popup_shown,
            "sku" => $this->product ? $this->product->sku : ''
        ];

        $relations = self::$relations;

        if (in_array('product', $relations)) {
            $data["sku"] = $this->product ? $this->product->url_key : '';
            $data["product_uuid"] = $this->product ? $this->product->uuid : '';
            $data["max_quantity"] = $this->product ? $this->product->alert_qty : '';
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

