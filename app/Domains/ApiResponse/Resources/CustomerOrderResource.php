<?php

namespace App\Domains\ApiResponse\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CustomerOrderResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): CustomerOrderResource
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

        if (in_array('simple', $relations)) {
            $order = [
                "id" => $this->id,
                "uuid" => $this->uuid,
                "order_number" => $this->order_number,
                "status" => $this->status,
                "payment_method" => $this->payment_method,
                "shipping_address" => $this->shipping_address ? json_decode($this->shipping_address) : null,
                "billing_address" => $this->billing_address ? json_decode($this->billing_address) : null,
                "area" => $this->area,
                "use_credit" => $this->use_credit,
                "delivery_date_time" => $this->delivery_date_time,
                "shipping_charge" => $this->shipping_charge,
                "coupon_code" => $this->coupon_code,
                "coupon_discount" => $this->coupon_discount,
                "total_tax" => $this->total_tax,
                "total_amount" => $this->total_amount,
                "transaction_id" => $this->transaction_id,
                "tracking_number" => $this->tracking_number,
                "created_at" => $this->created_at,
            ];
        } else {
            $order = [
                "id" => $this->id,
                "uuid" => $this->uuid,
                "order_number" => $this->order_number,
                "status" => $this->status,
                "payment_method" => $this->payment_method,
                "shipping_method" => $this->shipping_method,
                "shipping_address" => $this->shipping_address ? json_decode($this->shipping_address) : null,
                "billing_address" => $this->billing_address ? json_decode($this->billing_address) : null,
                "area" => $this->area,
                "use_credit" => $this->use_credit,
                "delivery_date_time" => $this->delivery_date_time,
                "shipping_charge" => $this->shipping_charge,
                "coupon_code" => $this->coupon_code,
                "coupon_discount" => $this->coupon_discount,
                "total_tax" => $this->total_tax,
                "total_amount" => $this->total_amount,
                "transaction_id" => $this->transaction_id,
                "tracking_number" => $this->tracking_number,
                "tracking_check_link" => $this->tracking_number,
                "created_at" => $this->created_at,
            ];
        }


        if (in_array('items', $relations)) {
            $order['items'] = CustomerOrderItemResource::collection($this->items, ['product', 'variations']);
        }


        if (in_array('returnOrder', $relations)) {
            $order['return_order'] = null;
        }

        $order["total_amount"] = $this->cart_total();

        return $order;
    }

    public function cart_total(): float
    {
        return $this->items->map(function ($item) {
            $data['total'] = $this->variationsSubTotal($item->variations);
            return $data;
        })->sum('total');
    }

    public function variationsSubTotal($variations)
    {
        return $variations->map(function ($q) {
            $data['variation_total'] = ($q->quantity * $q->sale_price);
            return $data;
        })->sum('variation_total');
    }
}
