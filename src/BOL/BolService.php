<?php

namespace Wappz\Sender\BOL;

use Wappz\Sender\Order;
use Wappz\Sender\Platform;

class BolService
{
    public $bol;
    public $order;

    public function __construct(Order $order, Platform $platform)
    {
        var_dump($this->order->toArray());
        $this->bol = new Bol\Bol($order->teampartner);
    }

    public function returnOrderItems() {

    }

    public function sendwithbol()
    {
        $orderitems = $this->returnOrderItems();
    }
}
