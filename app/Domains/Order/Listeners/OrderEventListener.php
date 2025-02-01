<?php

namespace App\Domains\Order\Listeners;

use App\Domains\Auth\Models\User;
use App\Domains\Order\Events\OrderPlacedCouponEvent;
use App\Domains\Order\Events\OrderPlacedNotificationEvent;
use App\Domains\Order\Events\OrderReturnEvent;
use App\Domains\Order\Events\OrderStatusEvent;
use App\Domains\Order\Events\TrackingOrderEvent;
use App\Domains\Order\Models\Coupon;
use App\Domains\Order\Models\CouponUser;
use App\Domains\Order\Models\OrderReturn;
use App\Domains\Order\Models\OrderReturnItem;
use App\Domains\Order\Models\OrderReturnItemVariation;
use App\Domains\Order\Models\OrderTracking;
use App\Notifications\ConfirmOrderNotification;
use App\Notifications\OrderCustomerNotification;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class OrderEventListener.
 */
class OrderEventListener
{
    /**
     * @param $event
     */
    public function orderPlacedCouponEvent($event)
    {
        $order = $event->order;
        $cart = $event->cart;
        $couponDiscount = $event->couponDiscount ?: $cart->coupon_discount;
        $couponCode = $cart->coupon_code;

        $today = now()->endOfDay();
        $coupon = Coupon::query()
            ->whereNotNull('active')
            ->where('coupon_code', $couponCode)
            ->whereDate('expiry_date', '>=', $today)
            ->first();

        if ($order && $coupon) {
            $order->coupon_code = $couponCode;
            $order->coupon_discount = $couponDiscount;
            $order->save();

            $log = new CouponUser();
            $log->coupon_id = $coupon->id;
            $log->coupon_code = $couponCode;
            $log->coupon_details = $couponDiscount;
            $log->win_amount = $couponDiscount;
            $log->order_id = $order->id;
            $log->user_id = $order->user_id;
            $log->save();
        }

        $cart->coupon_code = null;
        $cart->coupon_discount = null;
        $cart->save();

        activity('coupon')
            ->performedOn($event->order)
            ->log(':causer.name ' . ' Coupon code effect successfully');

    }

    /**
     * @param $event
     */
    public function orderPlacedNotificationEvent($event)
    {
        $order = $event->order;
        $customer = User::query()->find($order->user_id);
        $appName = config('app.name');
        if ($customer) {
            $text = "Thank you for choosing {$appName} for your recent purchase! We are delighted to confirm that we have received your order and it is currently being processed by our team.";
            $order_number = $order->order_number ?? '';
            $order_total = ($order->total_amount + $order->shipping_charge);
            $purchase_date = Carbon::parse($order->created_at)->format('d-m-Y');
            $text2 = "Here are the details of your order: <br/> Order Number: <b>$order_number</b>  <br/> Date of Purchase : <b>$purchase_date</b> <br/>Total Cost : $ <b>$order_total</b>";
            $customer->notify(new \App\Notifications\OrderCustomerNotification(['subject' => 'Order Placed Successfully', 'text' => $text, 'text2' => $text2]));
        }
        $adminUsers = User::query()->role('Administrator')->get();
        foreach ($adminUsers as $recipient) {
            $recipient->notify(new \App\Notifications\ConfirmOrderNotification(['order' => $order]));
        }
        activity('order')
            ->performedOn($event->order)
            ->log(':causer.name ' . ' order placed successfully');
    }

    /**
     * @param $event
     */
    public function orderStatusEvent($event)
    {
        try {
            $order = $event->order;
            $subject = $event->subject;
            $text = $event->text;
            $tracking = $order->tracking_number;
            $status = $order->status;

            $customer = User::query()->find($order->user_id);
            $order_number = $order->order_number ?? '';
            $order_total = ($order->total_amount + $order->shipping_charge);

            $text2 = "Here are the details of your order: <br/> Order Number: <b>$order_number</b>  <br/> Current Status : <b>$status</b> <br/>Total Cost : $ <b>$order_total</b>  <br/>Tracking : # <b>$tracking</b>";
            $customer->notify(new \App\Notifications\OrderCustomerNotification(['subject' => $subject, 'text' => $text, 'text2' => $text2]));
        } catch (\Exception $e) {

        }

    }


    /**
     * @param $event
     * @throws \Throwable
     */
    public function orderReturnEvent($event)
    {
        DB::beginTransaction();
        try {

            $order = $event->order;
            $orderItems = $order->items;
            $reason = $order->reason ?? ''; // if instance created
            $reasonImg = $order->reasonImg ?? ''; // if instance created
            $store_id = $order->store_id ?? '';
            $user = $order->user;
            $appName = config('app.name');

            $hasReturn = OrderReturn::query()
                ->where('order_id', $order->id)
                ->where('user_id', $order->user_id)
                ->where('order_id', $store_id)
                ->first();

            if ($user) {
                $return = $hasReturn ?: new OrderReturn();
                $return->order_id = $order->id;
                $return->return_reason = $reason ?: 'order returned by admin';
                $return->prof_picture = $reasonImg ?: null;
                $return->status = 'in-progress';
                $return->note = 'order returned by seller';
                $return->user_id = $user->id;
                $return->store_id = $store_id;
                $return->delivery_person_id = null;
                $return->save();

                $returnId = $return->id ?? null;

                foreach ($orderItems as $orderItem) {
                    $hasItem = OrderReturnItem::query()
                        ->where('user_id', $user->id)
                        ->where('order_return_id', $return->id)
                        ->where('order_item_id', $orderItem->id)
                        ->where('product_id', $orderItem->product_id)
                        ->where('store_id', $orderItem->store_id)
                        ->first();

                    $item = $hasItem ?: new OrderReturnItem();
                    $item->user_id = $user->id;
                    $item->order_return_id = $returnId;
                    $item->order_item_id = $orderItem->id;
                    $item->product_id = $orderItem->product_id;
                    $item->store_id = $orderItem->store_id;
                    $item->save();
                    $itemId = $item->id ?? null;
                    $userId = $user->id ?? null;
                    $variations = $orderItem->variations;
                    if ($variations->count() > 0) {
                        foreach ($variations as $variation) {
                            $variationItem = OrderReturnItemVariation::query()
                                ->where('order_return_id', $returnId)
                                ->where('return_item_id', $itemId)
                                ->where('user_id', $userId)
                                ->where('config_id', $variation->config_id)
                                ->first();
                            $variationItem = $variationItem ?: new OrderReturnItemVariation();
                            $variationItem->order_return_id = $returnId;
                            $variationItem->return_item_id = $itemId;
                            $variationItem->config_id = $variation->config_id;
                            $variationItem->attributes = $variation->attributes;
                            $variationItem->regular_price = $variation->regular_price;
                            $variationItem->sale_price = $variation->sale_price;
                            $variationItem->discount_amt = $variation->discount_amt;
                            $variationItem->quantity = $variation->quantity;
                            $variationItem->max_quantity = $variation->max_quantity;
                            $variationItem->user_id = $userId;
                            $variationItem->save();
                        }
                    }
                }
                $return->fresh();

                $text = "Thank you for choosing {$appName} for your recent purchase! We are delighted to confirm that we have received your order and it is currently being processed by our team.";
                $order_number = $order->order_number ?? '';
                $total_amount = $return->items
                    ->map(function ($item) {
                        $data['total'] = $item->variations->map(function ($variation) {
                            $sale_price = $variation->discount_amt ?: $variation->sale_price;
                            $data['variation_total'] = (int)$variation->quantity * (int)$sale_price;
                            return $data;
                        })->sum('variation_total');
                        return $data;
                    })->sum('total');
                $order_total = (int)$total_amount + (int)$order->shipping_charge;

                $text2 = "Your order has been returned. Order details are given below: <br/> Order Number: <b>$order_number</b> <br/>Total Cost : $ <b>$order_total</b>";
                $user->notify(new OrderCustomerNotification(['subject' => 'Order Returned Successfully', 'text' => $text, 'text2' => $text2]));
            }
            $adminUsers = User::query()->role('Administrator')->get();
            foreach ($adminUsers as $recipient) {
                $recipient->notify(new ConfirmOrderNotification(['order' => $order]));
            }
            activity('order')
                ->performedOn($event->order)
                ->log(':causer.name ' . ' order returned successfully');

            DB::commit();

        } catch (\Exception $df) {
            activity('order')
                ->performedOn($event->order)
                ->log(':causer.name ' . ' order returned log failed');
            DB::rollBack();
        }

    }


    /**
     * @param $event
     */
    public function trackingOrderEvent($event)
    {
        $order = $event->order;
        $order_id = $order->id;
        $user_id = $order->user_id;
        $orderStatus = $order->status;
        $allStatus = ['new', 'in-progress', 'cancel', 'dispatch', 'return', 'delivered'];

        if (!in_array($orderStatus, $allStatus)) {
            return false;
        }

        $tracking = OrderTracking::withTrashed()
            ->where('order_id', $order_id)
            ->where('user_id', $user_id)
            ->whereIn('status', $allStatus)
            ->get();
        if ($tracking->count() == count($allStatus)) {
            $tracking = $tracking->where('status', $order->status)->first();
            $tracking->updated_time = now();
            $tracking->user_id = $user_id;
            $tracking->deleted_at = null;
            $tracking->save();
        } else {
            $updating = true;
            foreach ($allStatus as $index => $status) {
                $newTracking = new OrderTracking();
                $newTracking->sorting = $index;
                $newTracking->order_id = $order_id;
                $newTracking->user_id = $user_id;
                $newTracking->status = $status;
                $newTracking->tracking_status = Str::title($status);
                if ($updating) {
                    $newTracking->updated_time = now();
                }
                if (in_array($status, ['return', 'cancel'])) {
                    $newTracking->deleted_at = now();
                }
                if ($status == $orderStatus) {
                    $newTracking->deleted_at = null;
                    $updating = false;
                }
                $newTracking->save();
            }
        }
        if (!$tracking->count()) {
            $tracking = OrderTracking::withTrashed()
                ->where('order_id', $order_id)
                ->where('user_id', $user_id)
                ->whereIn('status', $allStatus)
                ->get();
        }

        $firstItem = $tracking->where('status', $orderStatus)->first();

        if ($orderStatus == 'dispatch' && $tracking->count()) {
            OrderTracking::withTrashed()
                ->where('order_id', $order_id)
                ->where('user_id', $user_id)
                ->whereIn('status', ['cancel', 'return'])
                ->delete();
        } else if ($orderStatus == 'cancel' && $firstItem) {
            OrderTracking::withTrashed()
                ->where('order_id', $order_id)
                ->where('user_id', $user_id)
                ->where('id', '>', $firstItem->id)
                ->delete();
        } else if ($orderStatus == 'return' && $firstItem) {
            OrderTracking::withTrashed()
                ->where('order_id', $order_id)
                ->where('user_id', $user_id)
                ->where('id', '>', $firstItem->id)
                ->delete();
        }


    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            OrderPlacedCouponEvent::class,
            'App\Domains\Order\Listeners\OrderEventListener@orderPlacedCouponEvent'
        );
        $events->listen(
            OrderPlacedNotificationEvent::class,
            'App\Domains\Order\Listeners\OrderEventListener@orderPlacedNotificationEvent'
        );
        $events->listen(
            OrderReturnEvent::class,
            'App\Domains\Order\Listeners\OrderEventListener@orderReturnEvent'
        );
        $events->listen(
            OrderStatusEvent::class,
            'App\Domains\Order\Listeners\OrderEventListener@orderStatusEvent'
        );
        $events->listen(
            TrackingOrderEvent::class,
            'App\Domains\Order\Listeners\OrderEventListener@trackingOrderEvent'
        );
    }
}
