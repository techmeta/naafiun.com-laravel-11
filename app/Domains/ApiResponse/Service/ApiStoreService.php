<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\ApiResponse\Resources\Api\ApiAttributeItemResource;
use App\Domains\ApiResponse\Resources\Api\ApiAttributeResource;
use App\Domains\ApiResponse\Resources\CustomerOrderResource;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Models\OrderReturn;
use App\Domains\Order\Models\OrderReturnItem;
use App\Domains\Order\Models\OrderTracking;
use App\Domains\Order\Models\StoreCredit;
use App\Domains\Products\Models\Attribute;
use App\Domains\Products\Models\AttributeItem;
use App\Traits\PaginationTraits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiStoreService.
 */
class ApiStoreService
{
    use PaginationTraits;

    public function attribute()
    {
        $query = Attribute::with(['user:id,name'])
            ->withCount('attributeItems');
        $data = $this->getPaginatedData($query, ['name', 'slug', 'description']);
        return ApiAttributeResource::collection($data, ['attribute_items_count', 'user'])->response()->getData(true);
    }

    public function attributeItem()
    {
        $query = AttributeItem::with(['user:id,name', 'attribute']);
        $data = $this->getPaginatedData($query, ['label', 'value']);
        return ApiAttributeItemResource::collection($data, ['attribute', 'user'])->response()->getData(true);
    }

    public function orders()
    {
        $page = \request('page', 1);
        $limit = \request('limit', 20);
        $page = ($page - 1);
        $offset = ($limit * $page);
        $auth_id = auth('sanctum')->id();
        $orders = Order::with('items', 'returnOrder')
            ->where('user_id', $auth_id)
            ->withSum('items', 'total_amount')
            ->orderByDesc('id')
            ->offset($offset)
            ->paginate($limit);

        return CustomerOrderResource::collection($orders, ['simple', 'items', 'returnOrder'])->response()->getData(true);
    }

    public function orderDetails($order_uuid): CustomerOrderResource
    {
        $auth_id = auth('sanctum')->id();
        $orders = Order::with(['returnOrder', 'items' => function ($item) {
            $item->with(['product', 'variations']);
        }])
            ->where('user_id', $auth_id)
            ->where('uuid', $order_uuid)
            ->first();

        return CustomerOrderResource::single($orders, ['simple', 'items', 'returnOrder', 'variations']);
    }

    public function trackingDetails($uuid)
    {
        $user_id = auth('sanctum')->id();
        $order = Order::query()
            ->where('user_id', $user_id)
            ->where('uuid', $uuid)
            ->first();

        return OrderTracking::query()
            ->where('order_id', $order->id)
            ->where('user_id', $user_id)
            ->orderByDesc('id')
            ->get(['id', 'status', 'tracking_status', 'sorting', 'updated_time', 'comment', 'user_id']);
    }

    public function cancelOrder($uuid): array
    {
        $user_id = auth('sanctum')->id();
        $order = Order::query()
            ->where('user_id', $user_id)
            ->where('uuid', $uuid)
            ->where('status', 'new')
            ->first();

        if ($order) {
            $order->status = 'cancel';
            $order->save();

            return [
                'status' => true,
                'message' => 'Order cancelled successfully'
            ];
        }

        return [
            'status' => false,
            'message' => 'Sorry! Order cancelled failed'
        ];
    }


    /**
     * @throws \Throwable
     */
    public function returnOrder($uuid): array
    {
        $reason = request('return_reason');

        $user_id = auth('sanctum')->id();
        $order = Order::query()
            ->with(['user', 'items' => function ($query) {
                $query->with('variations')->withCount('variations');
            }])
            ->where('user_id', $user_id)
            ->where('uuid', $uuid)
            ->where('status', 'delivered')
            ->first();

        if ($order) {
            DB::beginTransaction();
            $process = true;
            try {
                $order->status = 'return';
                $order->save();
                $order->refresh();

                $reasonImg = null;
                if (request()->hasFile('reason_img')) {
                    $reasonImg = request()->file('reason_img');
                    $reasonImg = store_picture($reasonImg, 'return');
                }

                $order->reason = $reason;
                $order->reasonImg = $reasonImg;
//                event(new OrderReturnEvent($order));
                DB::commit();
            } catch (\Exception $exception) {
                Log::error($exception->getFile() . '::' . $exception->getLine() . ' :: ' . $exception->getMessage());
                DB::rollBack();
                $process = false;
            }

            return [
                'status' => $process,
                'message' => $process ? 'Return submitted successfully!' : 'sorry! Data not found!'
            ];
        }

        return [
            'status' => false,
            'message' => 'Sorry! Order cancelled failed'
        ];
    }

    public function reserve_orders()
    {
        $auth_id = auth('sanctum')->id();
        $reserve = Order::with('items', 'returnOrder')
            ->where('user_id', $auth_id)
            ->whereHas('items', function ($items) {
                $items->where('status', 'reserve');
            })
            ->withSum('items', 'total_amount')
            ->orderByDesc('id')
            ->get();
        return [
            'reserve' => $reserve
        ];
    }

    public function returns()
    {
        $auth_id = auth('sanctum')->id();
        $orders = OrderReturn::with('items', 'order')
            ->where('user_id', $auth_id)
            ->withSum('items', 'total_amount')
            ->orderByDesc('id')
            ->get();

        return [
            'returns' => $orders
        ];
    }

    public function store_credit()
    {
        $auth_id = auth('sanctum')->id();
        $credit = StoreCredit::where('user_id', $auth_id)
            ->orderByDesc('id')
            ->get();

        return [
            'credit' => $credit
        ];
    }

    public function return_orders()
    {
        $prof_picture = request('prof_picture');
        $selected = request('selected', []);
        $return_reason = request('return_reason');
        $order_id = request('order_id');
        $auth_id = auth('sanctum')->id();

        $orders = Order::where('user_id', $auth_id)
            ->where('id', $order_id)
            ->first();
        $orderItems = OrderItem::whereIn('id', $selected)->where('user_id', $auth_id)->get();

        if ($orderItems->count()) {
            $return = new  OrderReturn();
            $return->order_id = $order_id;
            $return->return_reason = $return_reason;
            $return->prof_picture = $prof_picture;
            $return->status = 'request-for-return';
            $return->user_id = $auth_id;
            $return->save();
            foreach ($orderItems as $item) {
                $r_item = new  OrderReturnItem();
                $r_item->order_return_id = $return->id;
                $r_item->order_item_id = $item->id;
                $r_item->product_id = $item->product_id;
                $r_item->name = $item->name;
                $r_item->original_price = $item->original_price;
                $r_item->sale_price = $item->sale_price;
                $r_item->discount_amount = $item->discount_amount;
                $r_item->quantity = $item->quantity;
                $r_item->sale_unit_id = $item->sale_unit_id;
                $r_item->total_amount = $item->total_amount;
                $r_item->attributes = $item->attributes;
                $r_item->status = 'request-for-return';
                $r_item->user_id = $auth_id;
                $r_item->save();

                $item->status = 'request-for-return';
                $item->save();
            }
        }
        return [
            'orders' => $orders
        ];
    }
}
