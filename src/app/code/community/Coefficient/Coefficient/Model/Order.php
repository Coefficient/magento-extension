<?php

class Coefficient_Coefficient_Model_Order
{
    public function parseOrder($order)
    {
        $this->order_id = $order->getId();
        $this->email = $order->getCustomerEmail();

        return $this;
    }
}
