<?php

namespace App\Domains\Order\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class OrderReturnEvent.
 */
class OrderReturnEvent
{
    use SerializesModels;

    public $order;

    /**
     * @param $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }
}
