<?php

namespace App\Domains\Cart\Services;

use App\Domains\ApiResponse\Service\AddressService;
use App\Domains\Cart\Models\Address;
use App\Domains\Cart\Models\CustomerCart;
use App\Domains\Cart\Models\CustomerCartItem;
use App\Domains\Cart\Models\CustomerCartItemVariation;
use App\Domains\Cart\Resources\CartResource;
use App\Domains\Order\Events\OrderPlacedCouponEvent;
use App\Domains\Order\Events\OrderPlacedNotificationEvent;
use App\Domains\Order\Models\Coupon;
use App\Domains\Order\Models\CouponUser;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Order\Models\OrderItemVariation;
use App\Domains\Order\Models\StoreCredit;
use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class CartService.
 */
class CartService
{

    public function fresh_cart()
    {
        $token = request('token');
        $token = request()->header('Cart-Token', $token);

        $auth_id = auth('sanctum')->id();
        $cart = CustomerCart::with(['items' => function ($item) {
            $item->with(['product:id,sku', 'book:id,url_key'])
                ->with('variations')
                ->withCount('variations');
        }])
            ->withCount('items')
            ->where('uuid', $token)
            ->first();

        if (!$cart) {
            $last_order = Order::query()->where('user_id', $auth_id)->orderByDesc('id')->first();
            $cart = CustomerCart::with(['items' => function ($item) {
                $item->with(['product:id,sku', 'book:id,url_key'])
                    ->with('variations')
                    ->withCount('variations');
            }])
                ->withCount('items')
                ->create([
                    'shipping_address' => $last_order?->shipping_address,
                    'user_id' => $auth_id ?: NULL,
                    'payment_method' => 'cod',
                ]);
        }
        if ($cart) {
            if (!$cart->user_id && $auth_id) {
                $cart->user_id = $auth_id;
                $cart->save();
            }
            if (!$cart->shipping_address && $auth_id) {
                $last_order = Order::query()->where('user_id', $auth_id)->orderByDesc('id')->first();
                $cart->shipping_address = $last_order?->shipping_address;
                $cart->payment_method = 'cod';
                $cart->save();
            }
        }
        $cart->fresh();

        return $cart;
    }


    public function cart_list(): CartResource
    {
        $cart = $this->fresh_cart();
        return CartResource::single($cart, ['items']);
    }

    public function storeItem(): CartResource
    {
        $cart = $this->fresh_cart();
        $product_id = request('item_id');

        $product = Book::where('id', $product_id)->first();

        if (!$product) {
            return CartResource::single($cart, ['items']);
        }

        $quantity = request('quantity');
        $config_sku = $product->id;
        $regular_price = $product->sale_price;
        $sale_price = $product->discount_price;
        $discount_amt = ((int)$regular_price - (int)$sale_price);
        $discount_amt = max($discount_amt, 0);
        $max_quantity = $product->order_limit;

        $cartItem = CustomerCartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->first();
        if (!$cartItem) {
            $cartItem = new CustomerCartItem();
            $cartItem->cart_id = $cart->id;
            $cartItem->product_id = $product->id;
            $cartItem->product_link = $product->url_key;
            $cartItem->store_id = $product?->store_id || 1;
            $cartItem->name = $product->name;
            $cartItem->picture = $product->book_cover_image;
            $cartItem->is_selected = true;
            $cartItem->is_cart = true;
            $cartItem->save();
        }
        $itemVariations = CustomerCartItemVariation::where('config_id', $config_sku)
            ->where('cart_id', $cart->id)
            ->where('cart_item_id', $cartItem->id)
            ->first();

        $itemVariations = $itemVariations ?: new CustomerCartItemVariation();
        $itemVariations->config_id = $config_sku;
        $itemVariations->cart_id = $cart->id;
        $itemVariations->cart_item_id = $cartItem->id;
        $itemVariations->attributes = null;
        $itemVariations->quantity = $quantity;
        $itemVariations->regular_price = $regular_price;
        $itemVariations->sale_price = $sale_price;
        $itemVariations->discount_amt = $discount_amt;
        $itemVariations->max_quantity = $max_quantity;
        $itemVariations->save();

        $cart->use_credit = null;
        $cart->save();
        $cart->refresh();

        return CartResource::single($cart, ['items']);
    }

    public function updateCart(): CartResource
    {
        $cart = $this->fresh_cart();
        $item_uuid = request('item_uuid');
        $variation_uuid = request('variation_uuid');
        $qty = request('quantity');
        $cart_item = CustomerCartItem::query()
            ->where('cart_id', $cart->id)
            ->where('uuid', $item_uuid)
            ->first();

        if (!$cart_item) {
            return CartResource::single($cart, ['items']);
        }

        $itemVariation = CustomerCartItemVariation::query()
            ->where('cart_id', $cart->id)
            ->where('cart_item_id', $cart_item->id)
            ->where('uuid', $variation_uuid)
            ->first();

        if (!$itemVariation) {
            return CartResource::single($cart, ['items']);
        }

        if ($qty) {
            $cart_item->is_selected = true;
            $cart_item->save();
            $itemVariation->quantity = $qty;
            $itemVariation->save();
        } else {
            $itemVariation->delete();
            $count = CustomerCartItemVariation::query()
                ->where('cart_id', $cart->id)
                ->where('cart_item_id', $cart_item->id)
                ->count();
            if (!$count) {
                $cart_item->delete();
            }
        }
        $cart->use_credit = null;
        $cart->save();
        $cart->refresh();

        return CartResource::single($cart, ['items']);
    }

    public function updateMarkAsCart(): CartResource
    {
        $cart = $this->fresh_cart();
        $product_id = request('item_id');
        $is_cart = request('is_cart');
        $read_popup = request('read_popup');

        $cart_item = CustomerCartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->first();
        if ($cart_item) {
            if ($is_cart !== null) {
                $cart_item->is_cart = $is_cart;
            }
            if ($read_popup !== null) {
                $cart_item->is_popup_shown = $read_popup;
            }
            $cart_item->save();
        }
        $cart->use_credit = null;
        $cart->save();
        $cart->refresh();

        return CartResource::single($cart, ['items']);
    }

    public function remove(): CartResource
    {
        $cart = $this->fresh_cart();
        $item_uuid = request('item_uuid');
        $items = CustomerCartItem::where('cart_id', $cart->id)
            ->where('uuid', $item_uuid)
            ->where('is_selected', '>', 0)
            ->pluck('id')
            ->toArray();
        if (count($items) > 0) {
            CustomerCartItemVariation::whereIn('cart_item_id', $items)->delete();
            CustomerCartItem::whereIn('id', $items)->delete();
            $cart->use_credit = null;
            $cart->save();
        }
        $cart->refresh();

        return CartResource::single($cart, ['items']);
    }

    public function toggle_cart_checkbox(): CartResource
    {
        $cart = $this->fresh_cart();
        $item_uuid = request('item_uuid');
        $is_checked = request('checked');

        if ($item_uuid) {
            CustomerCartItem::query()
                ->where('uuid', $item_uuid)
                ->where('cart_id', $cart->id)
                ->update(['is_selected' => (int)$is_checked]);
        } else {
            CustomerCartItem::query()->where('cart_id', $cart->id)
                ->update(['is_selected' => (int)$is_checked]);
        }

        $cart->use_credit = null;
        $cart->save();
        $cart->refresh();

        return CartResource::single($cart, ['items']);
    }

    public function addShippingToCard(): CartResource
    {
        $cart = $this->fresh_cart();
        $address = request('address', []);
//        $shipping_uuid = request('uuid');
//        $auth_id = auth('sanctum')->id();
//        $address = Address::query()
//            ->where('user_id', $auth_id)
//            ->where('uuid', $shipping_uuid)
//            ->first();

        $address = [
            'id' => $address['id'] ?? '',
            'uuid' => $address['uuid'] ?? '',
            'name' => $address['name'] ?? '',
            'phone' => $address['phone'] ?? '',
            'zip_code' => $address['zip_code'] ?? '',
            'city' => $address['city'] ?? '',
            'address' => $address['address'] ?? '',
            'company' => null,
            'tax_id' => null,
            'state' => null,
        ];
        $address = json_decode(json_encode($address));

        if ($cart && $address) {
            $cart = (new AddressService())->updateAddressToCart($cart, $address);
        }

        return CartResource::single($cart, ['items']);
    }

//     ================= Below items are not tested ==============


    public function useStoreCredit(Request $request): array
    {
        $token = $request->token;
        $cart = $this->fresh_cart($token);
        $use_credit = request('use_credit');
        $auth_id = auth('sanctum')->id();
        $credit_amt = null;
        $cart_payable = $cart->cart_payable;
        if ($use_credit) {
            $balance = StoreCredit::query()->where('user_id', $auth_id)->sum('added_deducted');
            if ($balance >= $cart_payable) {
                $credit_amt = $cart_payable;
            } else {
                $credit_amt = $balance;
            }
        }
        $cart->use_credit = $credit_amt;
        $cart->save();

        return ['cart' => $cart];
    }

    public function cut_of_time(Request $request): array
    {
        $token = $request->token;
        $method = request('method', []);
        $cart = $this->fresh_cart($token);
        if ($cart && count($method)) {
            $cart->shipping_method = json_encode($method);
            $cart->shipping_cost = $method['cost'] ?? null;
        }
        $cart->use_credit = null;
        $cart->save();

        $cart->refresh();
        return ['cart' => $cart];
    }

    public function addPaymentMethod(): CartResource
    {
        $pmt = request('method');
        $cart = $this->fresh_cart();
        if ($cart && $pmt) {
            $cart->payment_method = $pmt;
            $cart->save();
            $cart->refresh();
        }

        return CartResource::single($cart, ['items']);
    }

    public function generate_order_number($id, $prefix = '255', $length = 5): string
    {
        return str_pad($id, $length, $prefix, STR_PAD_LEFT);
    }

    private function checked_items_cart_total($cart, $store_id = null)
    {
        $items = $cart->items;
        if ($store_id) {
            $items = $items->where('store_id', $store_id);
        }
        return $items->where('is_selected', 1)
            ->map(function ($item) {
                $data['total'] = $item->variations->map(function ($variation) {
                    $data['variation_total'] = ($variation->quantity * $variation->sale_price) ?? 0;
                    return $data;
                })->sum('variation_total');
                return $data;
            })->sum('total');
    }

    /**
     * @throws \Throwable
     */
    public function placedOrder(): array
    {
        $cart = $this->fresh_cart();
        $auth_id = auth('sanctum')->id();
        $process = \request('process');
        $total_init = $this->checked_items_cart_total($cart);
        if (!$total_init && !$process) {
            return [
                'data' => null,
                'message' => 'Sorry! Your cart is empty.',
                'code' => 422,
            ];
        }

        $code = 200;
        $msg = 'Order placed successfully!';
        DB::beginTransaction();
        try {
            if ($cart) {
                $stores = $cart->items->pluck('store_id');
                $pmt_method = $cart->payment_method;
                $commission = 10;
                $couponDiscount = $cart->coupon_discount > 0 ? ($cart->coupon_discount / count($stores)) : 0;

                foreach ($stores as $store_id) {

                    $orderTotal = $this->checked_items_cart_total($cart, $store_id);

                    $order = new Order();
                    $order->shipping_address = $cart->shipping_address;
                    $order->billing_address = $cart->billing_address;
                    $order->payment_method = $pmt_method;
                    $order->shipping_charge = $cart->shipping_cost;
                    $order->total_amount = $orderTotal;
                    $order->area = null;
                    $order->paid = null;
                    $order->coupon_code = null;
                    $order->coupon_discount = null;
                    $order->total_tax = null;
                    $order->transaction_id = 'tr-' . Str::lower(uniqid());
                    $order->status = 'new'; // future unpaid
                    $order->user_id = $auth_id;
                    $order->save();

                    // payment_method
                    $storeItems = $cart->items;

                    $total_init = 0;
                    foreach ($storeItems as $item) {
                        $variation = $item->variations->first();

                        $price = $variation->sale_price;
                        $variation_total = (int)$variation->quantity * (int)$price;
                        $total_init += $variation_total;

                        $order_item = new OrderItem();
                        $order_item->product_id = $item->product_id;
//                        $order_item->store_id = $item->store_id;
                        $order_item->name = $item->name;
                        $order_item->price = $variation->sale_price;
                        $order_item->actual_price = $variation->regular_price;
                        $order_item->discount_amount = $variation->discount_amt;
                        $order_item->qty = $variation->quantity;
                        $order_item->sale_unit = $item->sale_unit;

                        $order_item->order_id = $order->id;
                        $order_item->user_id = $auth_id;
                        $order_item->status = 'new'; // future unpaid
                        $order_item->save();
                    }

                    $order->order_number = $this->generate_order_number($order->id);
                    $order->total_amount = $total_init;
                    $order->save();

                    if ($couponDiscount && $cart->coupon_code) {
                        event(new OrderPlacedCouponEvent($order, $cart, $couponDiscount));
                    }
                }


                CustomerCartItem::query()
                    ->where('cart_id', $cart->id)
                    ->delete();
                CustomerCartItemVariation::query()
                    ->where('cart_id', $cart->id)
                    ->delete();

                $cart->is_purchase = $cart->is_purchase + 1;
                $cart->save();

                DB::commit();
            }
        } catch (\Exception $ex) {
            $data = null;
            $code = 422;
            $msg = $ex->getMessage();
            DB::rollBack();
        }

        return [
            'data' => CartResource::single($cart, ['items']),
            'message' => $msg,
            'code' => $code,
        ];
    }


    public function coupon_code_submit($coupon_code): array
    {
        $cart = $this->fresh_cart();
        $cartTotal = $this->checked_items_cart_total($cart);
        $discount = $this->validateAppCoupon($coupon_code, $cartTotal);
        $status = $discount['status'] ?? false;

        if (!$status) {
            $cart->coupon_code = null;
            $cart->coupon_discount = null;
            $cart->save();
            $cart->refresh();
            return ['cart' => $cart, 'status' => $status, 'msg' => 'Coupon is not valid'];
        }
        $amount = $discount['amount'] ?? '';
        if ($amount == 'free_shipping') {
            $discount['amount'] = $amount;
        } else {
            $discount['amount'] = (int)$amount;
        }

        $c_amount = $discount['amount'];
        $status = false;
        $msg = 'Sorry! Coupon is not valid for this amount!';
        if ($c_amount > 0) {
            $cart->coupon_code = $coupon_code;
            $cart->coupon_discount = $c_amount;
            $cart->save();
            $status = true;
            $msg = 'Coupon added successfully';
        }
        $cart->refresh();
        return [
            'status' => $status,
            'cart' => $cart,
            'c_amount' => $c_amount,
            'msg' => $msg
        ];
    }

    public function coupon_reset()
    {
        $cart = $this->fresh_cart();
        if ($cart) {
            $cart->coupon_code = null;
            $cart->coupon_discount = null;
            $cart->save();
        }
        return $cart;
    }

    private function validateAppCoupon($coupon_code, $cartTotal): array
    {
        $today = now()->endOfDay();
        $coupon = Coupon::query()
            ->whereNotNull('active')
            ->where('coupon_code', $coupon_code)
            ->whereDate('expiry_date', '>=', $today)
            ->first();
        $data['status'] = false;
        if ($coupon) {
            $minimum_spend = $coupon->minimum_spend;
            $maximum_spend = $coupon->maximum_spend;
            $amount = 0;
            if ($minimum_spend && $maximum_spend) {
                $amount = ($cartTotal >= $minimum_spend && $cartTotal <= $maximum_spend) ? $coupon->coupon_amount : 0;
            } else if ($minimum_spend) {
                $amount = $cartTotal >= $minimum_spend ? $coupon->coupon_amount : 0;
            } else if ($maximum_spend) {
                $amount = $cartTotal <= $maximum_spend ? $coupon->coupon_amount : 0;
            } else {
                $amount = $coupon->coupon_amount;
            }

            $isEnable = false;
            if ($amount) {
                $isEnable = true;
                $limit_per_coupon = $coupon->limit_per_coupon;
                $limit_per_user = $coupon->limit_per_user;
                if ($limit_per_coupon) {
                    $countCoupon = CouponUser::query()->where('coupon_id', $coupon->id)->count();
                    $isEnable = $countCoupon <= $limit_per_coupon;
                }
                if ($limit_per_user) {
                    $user_id = auth('sanctum')->id();
                    $countUser = CouponUser::query()->where('coupon_id', $coupon->id)->where('user_id', $user_id)->count();
                    $isEnable = $countUser <= $limit_per_user;
                }
            }
            $mxc_amount = $coupon->max_coupon_amount ?: 5000;
            if ($coupon->coupon_type == 'flat' && $isEnable) {
                $c_amount = $amount;
                $data['amount'] = min($c_amount, $mxc_amount);
                $data['status'] = true;
            } else if ($coupon->coupon_type == 'percentage' && $isEnable) {
                $c_amount = $amount;
                $amount = ($cartTotal * $c_amount) / 100;
                $amount = min($amount, $mxc_amount);
                $data['amount'] = $amount;
                $data['status'] = true;
                $data['text'] = $c_amount . '% Percentage Discount';
            } else if ($coupon->coupon_type == 'free_shipping' && $isEnable) {
                $data['status'] = true;
                $data['text'] = 'Free Shipping Discount';
                $data['amount'] = 'free_shipping';
            }
        }
        return $data;
    }
}
