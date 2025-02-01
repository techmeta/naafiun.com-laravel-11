<?php

namespace App\Domains\Cart\Http\Controllers;

use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCartController extends Controller
{

    public CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }


    public function currentCartList(): JsonResponse
    {
        $cart = $this->cartService->cart_list();
        return $this->success($cart, 'current cart list');
    }


    public function addToCart(): JsonResponse
    {
        $cart = $this->cartService->storeItem();
        return $this->success($cart, 'add to cart');
    }

    public function updateCustomerCart(): JsonResponse
    {
        $cart = $this->cartService->updateCart();
        return $this->success($cart, 'update to cart');
    }

    public function updateAsCartItem(): JsonResponse
    {
        $cart = $this->cartService->updateMarkAsCart();
        return $this->success($cart, 'item mark as add to cart');
    }

    public function removeFromCart(): JsonResponse
    {
        $cart = $this->cartService->remove();
        return $this->success($cart);
    }

    public function updateCartCheckbox(): JsonResponse
    {
        $cart = $this->cartService->toggle_cart_checkbox();
        return $this->success($cart, 'item checked to purchase successfully');
    }

    public function choose_shipping(): JsonResponse
    {
        $cart = $this->cartService->addShippingToCard();
        return $this->success($cart, 'Shipping added successfully');
    }

    public function addPaymentMethod(): JsonResponse
    {
        $cart = $this->cartService->addPaymentMethod();
        return $this->success($cart, 'Payment method added successfully');
    }


    /**
     * @throws \Throwable
     */
    public function placedOrder(): JsonResponse
    {
        $response = $this->cartService->placedOrder();
        $data = $response['data'];
        $message = $response['message'];
        $code = $response['code'];


        return $this->success($data, $message, $code);
    }

//     ========== Blow are not tested =================================================================


    public function storeCredit(Request $request)
    {
        $cart = $this->cartService->useStoreCredit($request);
        return response($cart);
    }

    public function cutOfTime(Request $request)
    {
        $cart = $this->cartService->cut_of_time($request);
        return response($cart);
    }

    public function couponCodeSubmit($remove = false): JsonResponse
    {
        $coupon_code = request('coupon_code');
        $remove = request('remove', $remove);
        if ($coupon_code) {
            $cart = $this->cartService->coupon_code_submit($coupon_code);
            return response()->json($cart);
        } elseif ($remove) {
            $cart = $this->cartService->coupon_reset();
            return response()->json(['status' => true, 'cart' => $cart, 'msg' => 'Coupon removed successfully']);
        }
        return response()->json(['status' => false, 'msg' => 'Coupon code not valid more!']);
    }



}
