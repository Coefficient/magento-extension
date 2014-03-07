<?php

class Coefficient_Coefficient_ApiController extends Mage_Core_Controller_Front_Action {

    public function authenticate() {
        return true;
    }

    public function customersAction() {
        if (!$this->authenticate()) {
            return $this; // Why?
        }

        #$customers = array(array('name' => 'Skyler', 'job' => 'Programmer'),
        #                   array('name' => 'Obama', 'job' => 'President'),
        #               );
        #

        #$order_detail = Mage::helper('coefficient')->getCustomerDetail("foo");

        $config = Mage::getConfig();
        $banana_url = $config->getNode('coefficient/banana_url');

        $customers = Mage::getModel('customer/customer')->getCollection();
        $this->customers = array();
        foreach ($customers as $customer) {
            error_log("customer id is " . $customer->getId());
            $this->customers[] = Mage::getModel('coefficient/customer')->parseCustomer($customer);
        }

        $this->getResponse()
            #->setBody(json_encode($customers))
            ->setBody(json_encode($this))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);

        return $this;
    }

    private function setJsonResponse()
    {
        $this->getResponse()
            ->setBody(json_encode($this))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
    }

    public function ordersAction() {
        if (!$this->authenticate()) {
            return $this;
        }

        $this->orders = array();
        $ordersCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'complete');

        foreach ($ordersCollection as $order) {
            if ($order && $order->getId()) {
                $this->orders[] = Mage::getModel('coefficient/order')->parseOrder($order);
            }
        }

        $this->getResponse()
            ->setBody(json_encode($this))
            ->setHeader('Content-type', 'application/json', true);

        return $this;

    }

    public function orderItemsAction()
    {
        $collection = Mage::getModel('sales/order_item')->getCollection();

        $this->orderItems = array();
        
        foreach($collection as $orderItem) {
            if ($orderItem && $orderItem->getId()) {
                $this->orderItems[] = Mage::getModel('coefficient/orderItem')->parseOrderItem($orderItem);
            }
        }

        $this->setJsonResponse();

        return $this;
    }

    public function formatOrderItem($oi)
    {
        $data = array();
        

        var_dump($data);
        return $data;
    }

    public function indexAction() {
        echo "Hello, Coefficient Index!";
    }

}
