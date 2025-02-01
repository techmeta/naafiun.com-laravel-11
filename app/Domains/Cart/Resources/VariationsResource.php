<?php

namespace App\Domains\Cart\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class VariationsResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): VariationsResource
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
            "uuid" => $this->uuid,
            "config_id" => $this->config_id,
            "attributes" => $this->attributes ? json_decode($this->attributes) : null,
            "regular_price" => $this->regular_price,
            "sale_price" => $this->sale_price,
            "discount_amt" => $this->discount_amt,
            "quantity" => $this->quantity,
            "max_quantity" => $this->max_quantity,
        ];

        /*$relations = self::$relations;
        if (in_array('variations', $relations)) {
            $data["variations"] = $this->product ? $this->product->sku : '';
        }*/

        $data['variation_total'] = ($this->quantity * $this->sale_price) ?? 0;

        return $data;
    }



}

