<?php

/**
 * Provide an API for integration with Coefficient.
 */
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

    public function testAction()
    {
        #$orderItem = Mage::getModel('sales/order_item')->load($orderItem->getId());
        $collection = Mage::getModel('sales/order_item')->getCollection();
        foreach ($collection as $item) {
            print_r($item->getData());
        }
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
    }

    public function ordersAction()
    {
        #if (!$this->authorize()) {
        #    return $this;
        #}

        #$properties = array(

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'complete')
            ->addAttributeToSelect('*');

        $orders = array();

        foreach ($collection as $order) {
            $orders[] = array(
                'order_id'    => $order->getId(),
                'customer_id' => $order->getCustomerId(),
                'store_id'    => $order->getStoreId(),
                'base_discount_amount' => $order->getBaseDiscountAmount(),
                'base_shipping_amount' => $order->getBaseShippingAmount(),
                'base_shipping_tax_amount' => $order->getBaseShippingTaxAmount(),
                'base_tax_amount'    => $order->getBaseTaxAmount(),
                'base_grand_total'   => $order->getBaseGrandTotal(),
                'base_currency_code' => $order->getBaseCurrencyCode(),
                'total_item_count'   => $order->getTotalItemCount(),
                'created_at'  => $order->getCreatedAt(),
            );
        }

        $this->sendCsvResponse($orders);
    }

    public function orderItemsAction()
    {
        $collection = Mage::getModel('sales/order_item')->getCollection();

        $orderItems = array();
        
        foreach($collection as $orderItem) {
            if ($orderItem && $orderItem->getId()) {
                $this->orderItems[] = Mage::getModel('coefficient/orderItem')->parseOrderItem($orderItem);
            }
        }

        $this->setJsonResponse();

        return $this;
    }

    /**
     * Build the Response object for a JSON response.
     */
    private function setJsonResponse()
    {
        $this->getResponse()
            ->setBody(json_encode($this))
            ->setHttpResponseCode(200)
            ->setHeader('Content-type', 'application/json', true);
    }

    /**
     * Write CSV content directly to the output stream.
     */
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

    /**
     * Send a CSV response directly to the outut stream.
     *
     * This bypasses Magento's Response object.
     */
    private function sendCsvResponse($rows)
    {
        header('Content-type: text/csv');
        $this->writeCsv($rows);
    }

}

?>
