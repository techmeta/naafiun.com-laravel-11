<?php

namespace App\Domains\Order\Http\Controllers;

use App\Domains\Order\Events\OrderReturnEvent;
use App\Domains\Order\Events\OrderStatusEvent;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Resources\AdminOrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;

class OrderController extends Controller
{

    private $excepts = ['return-progress', 'returned'];

    public function list(): JsonResponse
    {
        if (!$this->hasPermission('order.list')) {
            return $this->noPermissionResponse();
        }

        $trash = request('trash');
        $currencyIcon = get_setting('currency_icon', '$');

        if ($trash) {
            $collection = Order::onlyTrashed()
                ->with(['user', 'items' => function ($items) {
                    $items->with('variations');
                }])
                ->withCount('items')
                ->latest()
                ->paginate();
        } else {
            $collection = Order::query()
                ->with(['user', 'items' => function ($items) {
                    $items->with('variations');
                }])
                ->withCount('items')
                ->latest()
                ->paginate();
        }

        $data = AdminOrderResource::collection($collection, ['currencyIcon' => $currencyIcon, 'items', 'user'])->response()->getData(true);
        return $this->success($data, 'order fetched successfully');
    }

    public function index()
    {
        return view('backend.orders.order.index');
    }


    public function show($order_id)
    {
        $excepts = $this->excepts;
        $order = Order::with(['user', 'items' => function ($query) use ($excepts) {
            $query->with('variations')->whereNotIn('status', $excepts);
        }])->withCount('items')->findOrFail($order_id);

        return view('backend.orders.order.show', compact('order'));
    }

    public function changeStatus($id)
    {
        $excepts = $this->excepts;
        $status = request('status');
        $order = Order::query()
            ->with(['user', 'items' => function ($query) {
                $query->with('variations')->withCount('variations');
            }])
            ->where('id', $id)
            ->first();

        if ($order) {
            $order->status = $status;
            $order->save();
            OrderItem::query()
                ->where('order_id', $order->id)
                ->whereNotIn('status', $excepts)
                ->update(['status' => $status]);
        }

        try {
            $customer = $order->user;
            if ($customer && in_array($status, ['in-progress', 'dispatch', 'delivered', 'return'])) {
                $order_number = $order->order_number ?? '';
                $status = $order->status ?? '';
                $subject = '';
                $text = '';
                if ($status == 'in-progress') {
                    $subject = 'Your Order is InProgress';
                    $text = "We're happy to let you know that we've received your order and start processing";
                } else if ($status == 'dispatch') {
                    $subject = 'Your Order is Dispatch';
                    $text = "We're happy to let you know that we've received your order. We are dispatch your order now";
                } else if ($status == 'delivered') {
                    $subject = 'Your package has been delivered!';
                    $text = "Thank you for choosing kitchentoolsbd.com! Item(s) from your order # $order_number has been delivered.";
                } else if ($status == 'return') {
                    $order->refresh();
                    event(new OrderReturnEvent($order));
                }

                if ($status !== 'return') {
                    event(new OrderStatusEvent($order, $subject, $text));
                }

            }

        } catch (\Exception $ex) {

        }

        return response(['order' => $order]);
    }

    public function trackingStore($id)
    {
        $tracking_number = request('tracking_number');
        $tracking_check_link = request('tracking_check_link');
        $order = Order::query()->where('id', $id)->first();
        if ($order) {
            $order->tracking_number = $tracking_number;
            $order->tracking_check_link = $tracking_check_link;
            $order->save();
        }
        return response(['order' => $order]);
    }

    public function destroy($id)
    {
        $order = Order::withTrashed()->find($id);
        $orderItem = OrderItem::withTrashed()->where('order_id', $id);
        if ($order->trashed()) {
            $order->forceDelete();
            $orderItem->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Order permanently deleted',
            ]);
        } else if ($order->delete()) {
            $orderItem->delete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Order moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }

    public function changeOrderStatus(Request $request): array
    {
        Order::query()
            ->where('id', $request->id)
            ->update(['status' => $request->status]);

        // return Redirect()->back()->with($notification);
        return ['message' => 'Order Status Successfully Update', 'alert-type' => 'success'];
    }

    public function orderInvoiceDownload($order_id)
    {
        $order = Order::with('items.unit', 'user')->where('id', $order_id)->first();
        // $orderItem = OrderItem::with('product')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        // return $orderItem;
        $pdf = PDF::loadView('backend.orders.order.show', compact('order'))->setPaper('a4')->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);

        return $pdf->download('invoice.pdf');
    }


    public function restore($id)
    {
        Order::onlyTrashed()->findOrFail($id)->restore();
        OrderItem::onlyTrashed()->where('order_id', $id)->restore();
        return redirect()->route('admin.order.inhouse.index')
            ->withFlashSuccess('Order Recovered Successfully');
    }
}
