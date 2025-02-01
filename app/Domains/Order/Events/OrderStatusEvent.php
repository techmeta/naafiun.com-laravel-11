<?php

namespace App\Domains\Order\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class OrderStatusEvent.
 */
class OrderStatusEvent
{
    use SerializesModels;

    public $order;
    public $subject;
    public $text;

    /**
     * @param $order
     * @param $subject
     * @param $text
     */
    public function __construct($order, $subject, $text)
    {
        $this->order = $order;
        $this->subject = $subject;
        $this->text = $text;
    }
}
