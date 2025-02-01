<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Auth\Models\User;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\OtpVerify;
use App\Domains\Products\Traits\SessionCart;
use App\Http\Controllers\Controller;
use App\Notifications\OrderAuthInformation;
use App\Notifications\OrderPending;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use SessionCart;

    public function verifyOrder2fa()
    {
        $shipping = request('shipping', []);
        $name = $shipping['name'] ?? '';
        $phone = $shipping['phone'] ?? '';
        $phone = str_replace('+88', '', $phone);
        $otp_code = mt_rand(100000, 999999);
        $uid = Str::random(60);;
        $status = false;
        if ($phone) {
            $otp = new OtpVerify();
            $otp->uid = $uid;
            $otp->otp_code = $otp_code;
            $otp->phone = $phone;
            $otp->save();
            if ($otp) {
                $appUrl = 'https://naafiun.com';
                $txt = "{$otp_code} is your One Time Password (OTP) for Naafiun 2FA, validity is 10 minutes. Helpline 01407700600 {$appUrl}";
                try {
                    $response = singleSms($txt, $phone);
                    $status = true;
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return response([
            'uid' => $uid,
            'phone' => $phone,
            'status' => $status,
        ]);
    }

    public function customerCart()
    {
        $customerCart = $this->getCart();
        return response([
            'cart' => $customerCart,
        ]);
    }

    public function productAddToCart()
    {
        $id = request('id');
        $quantity = request('quantity', 1);
        $product = Book::with('saleUnit')->find($id);
        $customerCart = $this->getCart();
        $currency = "৳";

        $cart = [];
        if ($product) {
            $items = getArrayKeyData($customerCart, 'items', []);
            $new = true;
            foreach ($items as $key => $item) {
                $item_id = getArrayKeyData($item, 'id', null);
                $item_qty = getArrayKeyData($item, 'quantity', 0);
                if ($item_id == $id) {
                    $items[$key]['quantity'] = ($quantity + $item_qty);
                    $items[$key]['sale_price'] = $product->sale_price;
                    $items[$key]['discount_price'] = $product->discount_price;
                    $new = false;
                }
            }

            if ($new) {
                $items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->url_key,
                    'sale_price' => $product->sale_price,
                    'discount_price' => $product->discount_price,
                    'image' => asset($product->book_cover_image),
                    'alert_qty' => $product->alert_qty,
                    'order_limit' => $product->order_limit,
                    'sale_unit' => $product->saleUnit->name ?? 'pcs',
                    'quantity' => $quantity,
                ];
            }

            $cart['currency'] = $currency;
            $cart['shipping_charge'] = get_setting('shipping_charge');
            $cart['items'] = $items;
            $cart['total_items'] = count($items);

            $this->updateCart($cart);

            return response([
                'cart' => $cart,
            ]);
        }

        return response([
            'status' => false,
            'cart' => [],
            'msg' => 'Product add to cart failed',
        ]);
    }

    public function productCartUpdate()
    {
        $id = request('id');
        $quantity = request('qty', 1);
        $product = Book::with('saleUnit')->find($id);
        $cart = $this->getCart();

        if ($product && $quantity) {
            if (!empty($cart) && is_array($cart)) {
                $items = getArrayKeyData($cart, 'items', []);
                foreach ($items as $key => $item) {
                    $item_id = getArrayKeyData($item, 'id', null);
                    if ($item_id == $id) {
                        $items[$key]['quantity'] = (int)$quantity;
                        $items[$key]['sale_price'] = $product->sale_price;
                        $items[$key]['discount_price'] = $product->discount_price;
                    }
                }

                $cart['items'] = $items;
                $cart['total_items'] = count($items);
                Session::put('customerCart', $cart);

                return response([
                    'cart' => $cart,
                ]);
            }
        }

        return response([
            'cart' => $cart,
            'msg' => 'Product update failed',
        ]);
    }


    public function removeFromCart($id)
    {
        $product = Book::with('saleUnit')->find($id);
        $cart = $this->getCart();
        if ($product) {
            $items = getArrayKeyData($cart, 'items', []);
            if (!empty($items)) {
                $newItems = [];
                foreach ($items as $key => $item) {
                    $item_id = getArrayKeyData($item, 'id', null);
                    if ($item_id != $id) {
                        array_push($newItems, $item);
                    }
                }
                $cart['items'] = $newItems;
                $cart['total_items'] = count($newItems);
                Session::put('customerCart', $cart);

                return response([
                    'cart' => $cart,
                ]);
            }
        }

        return response([
            'cart' => $cart,
            'msg' => 'Product update failed',
        ]);
    }


    public function saveShippingAddress()
    {
        $shipping = request()->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'city' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'address' => 'required|string|max:400',
        ]);

        $cart = $this->getCart();

        $cart['shipping_address'] = $shipping;
        $this->updateCart($cart);

        $auth = Auth::user();
        if ($auth) {
            if ($auth->name == 'OTP User') {
                $user = User::find($auth->id);
                $user->name = request('name');
                $user->save();
            }
        }

        return response([
            'cart' => $cart,
        ]);
    }

    public function confirmCustomerOrder()
    {
        $payment = request()->validate([
            'payment_option' => 'required|string|max:255',
        ]);

        $uid = request('uid');
        $userPhone = request('userPhone');
        $otp_code = request('otp_code');

        try {
            $minutes = Carbon::now()->subMinutes(10)->toDateTimeString();
            OtpVerify::query()->where('created_at', '<=', $minutes)->delete();
            Log::info('Expired OTP deleted successfully');
        } catch (\Exception $ex) {
            Log::error('Expired OTP deletion failed::' . $ex->getMessage());
        }

        $hasVerify = OtpVerify::query()
            ->where('uid', $uid)
            ->where('phone', $userPhone)
            ->where('otp_code', $otp_code)
            ->first();

        if (!$hasVerify && 0) {
            Log::warning('OTP Verification Failed, Phone:: ' . $userPhone . ', OTP Code:: ' . $otp_code);
            return response([
                'status' => false,
                'msg' => 'Sorry! OTP Verification Failed. Type your right otp code!',
            ]);
        }

        $cart = $this->getCart();

        if (!empty($cart)) {
            $items = getArrayKeyData($cart, 'items', []);
            $shipping_address = getArrayKeyData($cart, 'shipping_address', []);
            $shipping_charge = getArrayKeyData($cart, 'shipping_charge', 60);
            if (!count($items) || !count($shipping_address)) {
                return response([
                    'status' => false,
                    'msg' => 'Sorry! You cannot order an empty cart, please try again'
                ]);
            }

            $area = getArrayKeyData($shipping_address, 'city');
            $payment_method = request('payment_option');
            $user_id = auth()->check() ? auth()->id() : null;
            $order = Order::create([
                'shipping_address' => json_encode($shipping_address),
                'billing_address' => null,
                'shipping_charge' => $shipping_charge,
                'area' => $area,
                'paid' => null,
                'coupon_code' => null,
                'coupon_discount' => null,
                'total_tax' => null,
                'total_amount' => null,
                'transaction_id' => null,
                'status' => 'new',
                'payment_method' => $payment_method,
                'user_id' => $user_id,
            ]);
            $order_id = $order->id;
            $total_amount = 0;

            foreach ($items as $item) {
                $quantity = getArrayKeyData($item, 'quantity', 0);
                $sale_price = getArrayKeyData($item, 'sale_price', 0);
                $discount_price = getArrayKeyData($item, 'discount_price');
                $price = $discount_price ?: $sale_price;
                $total_amount += ($quantity * $price);
                OrderItem::create([
                    'product_id' => getArrayKeyData($item, 'id'),
                    'name' => getArrayKeyData($item, 'name'),
                    'price' => $price,
                    'actual_price' => $sale_price,
                    'discount_amount' => ($sale_price - $price),
                    'qty' => $quantity,
                    'sale_unit' => getArrayKeyData($item, 'sale_unit'),
                    'status' => 'new',
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ]);
            }

            $order->update([
                'order_number' => generate_zero_prefix_number($order_id),
                'total_amount' => $total_amount,
            ]);

            try {
                $users = User::role('administrator')->get();
                Notification::send($users, new OrderAuthInformation($order));
                $address = json_decode($order->shipping_address, true) ?? [];
                $phone = $address['phone'] ?? null;
                if ($order->user) {
                    if ($order->user->email) {
                        $order->user->notify(new OrderPending($order));
                        Log::info("Customer order placed notification send to email successfully::");
                    }
//                    $phone = $order->user->phone ?: $phone;
                }
                if ($phone) {
                    $appUrl = 'https://naafiun.com';
                    $txt = "As Salamu Alaikum,\nYour order no. #{$order->order_number} has been received. We are processing your order now, and we’ll let you know when it ships.\nJazakumullahu Khayran\nHelpline 01407700600, {$appUrl}";
                    $response = singleSms($txt, $phone);
                    Log::info("Order complete OTP send to customer:: {$phone} and Text::" . $txt);
                }
            } catch (\Exception $ex) {
                Log::error('Confirm Order Notification Failed::' . $ex->getMessage());
            }

        }

        $this->clearCart();

        return response([
            'status' => true,
            'msg' => 'Order Placed successfully',
        ]);

    }
}
