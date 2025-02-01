<?php

namespace App\Domains\Order\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/**
 * Class TrackingOrderEvent.
 */
class TrackingOrderEvent
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
