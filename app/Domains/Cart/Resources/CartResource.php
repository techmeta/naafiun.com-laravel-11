<?php

namespace App\Domains\Cart\Resources;

use App\Domains\Order\Models\Coupon;
use App\Domains\Order\Models\StoreCredit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): CartResource
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
        $return = [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "shipping_address" => $this->shipping_address ? json_decode($this->shipping_address, true) : null,
            "billing_address" => $this->billing_address ? json_decode($this->billing_address, true) : null,
            "payment_method" => $this->payment_method,
            "shipping_method" => $this->shipping_method ? json_decode($this->shipping_method) : null,
            "shipping_cost" => $this->shipping_cost,
            "coupon_code" => $this->coupon_code,
            "coupon_discount" => (int)$this->coupon_discount,
            "use_credit" => $this->use_credit,
            "status" => $this->status,
            "is_purchase" => $this->is_purchase,
        ];

        $cart_total = $this->cart_total();
        $return["cart_total"] = $cart_total;
        $return["checked_items_cart_total"] = $this->checked_items_cart_total();
        $return["has_coupon"] = $this->has_coupon($cart_total);

        $relations = self::$relations;

        if (in_array('items', $relations)) {
            $return['items'] = CartItemResource::collection($this->items, ['variations']);
        }

        return $return;
    }

    public function checked_items_cart_total()
    {
        return $this->items->where('is_selected', 1)->where('is_cart', 1)->map(function ($item) {
            $data['item_total'] = $this->variationsSubTotal($item->variations);
            return $data;
        })->sum('item_total');
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


    public function has_coupon($cart_total): bool
    {
        if (!$cart_total) {
            return false;
        }
        $auto_coupon = get_setting('auto_apply_coupon_code');
        $today = now()->endOfDay();
        return Coupon::query()
                ->whereNotNull('active')
                ->where('coupon_code', '!=', $auto_coupon)
                ->where('expiry_date', '>', $today)
                ->count() > 0;
    }

}

