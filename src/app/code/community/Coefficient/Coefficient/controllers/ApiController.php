<?php

class Coefficient_Coefficient_ApiController extends Mage_Core_Controller_Front_Action
{

    private function notAuthorized()
    {
        error_log("sending HTTP 403");
        Mage::helper('coefficient')->log("sending HTTP 403");
        $this->getResponse()->setHttpResponseCode(403)
            ->setBody("Not authorized.");
    }

    private function getRequestApiKey()
    {
        $auth_header = $this->getRequest()->getHeader('Authorization');

        $matches = array();
        preg_match('/token apiKey="(.+)"/', $auth_header, $matches);
        if (isset($matches[1])) {
            return trim($matches[1]);
        }
        return null;
    }

    private function authorize()
    {
        /*if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
            $this->notAuthorized();
            Mage::log("HTTPS isn't on.");
            return false;
        }*/

        $apiKey = $this->getRequestApiKey();

        if (!$apiKey) {
            $this->notAuthorized();
            Mage::log("No API key in request authorization header.");
            return false;
        }
        
        if ($apiKey != Mage::helper('coefficient')->getApiKey()) {
            $this->notAuthorized();
            Mage::log("API keys don't match.");
            return false;
        }
        
        if (!Mage::getStoreConfig('coefficient/api/enabled')) {
            $this->notAuthorized();
            Mage::log("API access isn't enabled.");
            return false;
        }

        return true;
    }

    public function customersAction()
    {
        if (!$this->authorize()) {
            return $this;
        }

        $config = Mage::getConfig();
        $banana_url = $config->getNode('coefficient/banana_url');

        $customersModel = Mage::getModel('coefficient/customers')->loadCustomers();

        $this->sendCsvResponse($customersModel->customers);

        #$this->getResponse()
        #    #->setBody(json_encode($customers))
        #    ->setBody(json_encode($customers))
        #    ->setHttpResponseCode(200)
        #    ->setHeader('Content-type', 'application/json', true);

        return $this;
    }

    private function setJsonResponse()
    {
        $this->getResponse()
            ->setBody(json_encode($this))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
    }

    public function ordersAction()
    {
        if (!$this->authorize()) {
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

    private function writeCsv(array $rows)
    {
        if (!$rows) {
            return;
        }
        $headers = array_keys($rows[0]);
        $fh = fopen('php://output', 'w');
        fputcsv($fh, $headers);
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);
    }

    private function sendCsvResponse($rows)
    {
        $this->getResponse()->setHeader('Content-type', 'text/csv');
        $this->writeCsv($rows);
    }

}
