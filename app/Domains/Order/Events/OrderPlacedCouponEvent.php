<?php

namespace App\Domains\Order\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class OrderPlacedCouponEvent.
 */
class OrderPlacedCouponEvent
{
    use SerializesModels;

    public $order;
    public $cart;
    public $couponDiscount;

    /**
     * @param $order
     * @param $cart
     */
    public function __construct($order, $cart, $couponDiscount)
    {
        $this->order = $order;
        $this->cart = $cart;
        $this->couponDiscount = $couponDiscount;
    }
}
