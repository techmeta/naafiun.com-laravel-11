<?php

namespace App\Domains\Order\Events;

use App\Domains\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

/**
 * Class OrderPlacedNotificationEvent.
 */
class OrderPlacedNotificationEvent
{
    use SerializesModels;


    public Order $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
