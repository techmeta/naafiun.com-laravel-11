<?php

namespace App\Domains\Order\Http\Controllers;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Models\OrderReturn;
use App\Domains\Order\Models\OrderReturnItem;
use App\Domains\Order\Models\StoreCredit;
use App\Http\Controllers\Controller;

class ReturnController extends Controller
{

    public function index()
    {
        return view('backend.orders.return.index');
    }

    public function show($order_id)
    {
        $return = OrderReturn::with(['user:id,name', 'order', 'items' => function ($item) {
            $item->with(['variations', 'product']);
        }])
            ->whereHas('order')
            ->withCount('items')
            ->where('id', $order_id)
            ->first();
        if (!$return) {
            abort(404);
        }
        return view('backend.orders.return.show', compact('return'));
    }

    public function update($id)
    {
        $status = request('status');
        $return = OrderReturn::with(['order', 'items'])
            ->withSum('items', 'total_amount')
            ->findOrFail($id);
        OrderItem::query()
            ->where('order_id', $return->order_id)
            ->update([
                'status' => $status
            ]);
        OrderReturnItem::query()
            ->where('order_return_id', $return->id)
            ->update([
                'status' => $status
            ]);

        if ($status === 'returned') {
            $last = StoreCredit::query()->where('user_id', $return->user_id)->orderByDesc('id')->first();
            $last_balance = $last ? $last->credit_balance : 0;
            $has_store = StoreCredit::query()->where('user_id', $return->user_id)
                ->where('reference_id', $return->id)
                ->where('action', 'add-credit')
                ->first();
            if (!$has_store) {
                $store = new StoreCredit();
                $store->reference_id = $return->id;
                $store->user_id = $return->user_id;
                $store->Location = $return->order->shipping_address ?? '';
                $store->credit_balance = $last_balance + $return->return_items_sum_total_amount;
                $store->added_deducted = $return->return_items_sum_total_amount;
                $store->action = 'add-credit';
                $store->comment = "Credited From Product RMA - {$return->order->order_number}";
                $store->save();
            }
        }
        return redirect()->back()->withFlashSuccess('Return status updated successfully');
    }
}
