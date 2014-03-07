<?php

class Coefficient_Coefficient_Model_OrderItem
{
    public function parseOrderItem($item)
    {
        $this->order_id = $item->getOrder()->getId();
        $this->customer_id = $item->getOrder()->getCustomerId();
        $this->created_at = $item->getCreatedAt();
        $this->weight = $item->getWeight();
        $this->sku = $item->getSku();
        $this->name = $item->getName();
        $this->description = $item->getDescription();
        $this->store = $item->getOrder()->getStore()->getCode();

        $this->qty_ordered = (int)$item->getQtyOrdered();
        $this->qty_refunded = (int)$item->getQtyRefunded();
        $this->qty_shipped = (int)$item->getQtyShipped();
        $this->qty_backordered = (int)$item->getQtyBackordered();

        $this->price = round($item->getPrice(), 2);
        $this->original_price = round($item->getOriginalPrice(), 2);
        $this->row_total = round($item->getRowTotal(), 2);
        $this->tax_percent = round($item->getTaxPercent(), 2);
        $this->tax_amount = round($item->getTaxAmount(), 2);
        $this->discount_percent = round($item->getDiscountPercent(), 2);
        $this->discount_amount = round($item->getDiscountAmount(), 2);

        return $this;
    }
}
