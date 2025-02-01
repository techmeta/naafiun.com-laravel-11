<?php

namespace App\Domains\Order\Resources;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AdminOrderResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): AdminOrderResource
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
        $currency = 'USD';
        if (array_key_exists('currencyIcon', $relations)) {
            $currency = $relations['currencyIcon'];
        }

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'created_at' => $this->created_at->format('d-M-Y'),

            'shipping_address' => $this->shipping_address,
            'payment_method' => $this->payment_method,
            'shipping_charge' => $this->shipping_charge,
            'status' => $this->status,
            'tracking_number' => $this->tracking_number,
            'note' => $this->note,
            'currency' => $currency
        ];

        if (in_array('user', $relations)) {
            $data['user'] = $this->user ? [
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ] : null;
        }

        if (in_array('items', $relations)) {
            $data['items_count'] = $this->items_count;
            $data['items'] = $this->items;
            $data['order_value'] = $this->items
                ->map(function ($item) {
                    $data['total'] = $item->variations->map(function ($variation) {
                        $sale_price = $variation->sale_price;
                        $data['variation_total'] = (int)$variation->quantity * (int)$sale_price;
                        return $data;
                    })->sum('variation_total');
                    return $data;
                })->sum('total');
        }


        return $data;
    }
}
