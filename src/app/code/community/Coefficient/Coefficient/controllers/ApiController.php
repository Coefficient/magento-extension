<?php

/**
 * Provide an API for integration with Coefficient.
 */
class Coefficient_Coefficient_ApiController extends Mage_Core_Controller_Front_Action
{

    private function notAuthorized()
    {
        Mage::helper('coefficient')->log(
            "{$_SERVER['REMOTE_ADDR']} not authorized: sending HTTP 403");

        $this->getResponse()
            ->setHttpResponseCode(403)
            ->setBody("Not isAuthorizedd.");
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

    private function isAuthorized()
    {
        /*if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
            $this->notAuthorized();
            Mage::log("The request isn't using HTTPS");
            return false;
        }*/

        $apiKey = $this->getRequestApiKey();

        $helper = Mage::helper('coefficient');

        if (!$apiKey) {
            $this->notAuthorized();
            $helper->log("No API key in request authorization header");
            return false;
        }
        
        if ($apiKey != Mage::helper('coefficient')->getApiKey()) {
            $this->notAuthorized();
            $helper->log("Incorrect API key");
            return false;
        }
        
        if (!Mage::getStoreConfig('coefficient/api/enabled')) {
            $this->notAuthorized();
            $helper->log("API access isn't enabled");
            return false;
        }

        return true;
    }

    public function versionAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }
        $version = Mage::helper('coefficient')->getExtensionVersion();
        $this->getResponse()->setBody($version);
    }

    public function customersAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }

        $collection = Mage::getResourceModel('customer/customer_collection')
               ->addNameToSelect()
               ->addAttributeToSelect('*')
               ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
               ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
               ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
               ->joinAttribute('billing_country_code', 'customer_address/country_id', 'default_billing', null, 'left');

        $customers = array();

        foreach ($collection as $customer) {
            $customers[] = array(
                'customerId' => $customer->getId(),
                'createdAt'  => $customer->getCreatedAt(),
                'email' => $customer->getEmail(),
                'name'  => $customer->getName(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname(),
                'gender'    => $customer->getAttributeText('gender'),
                'dob'       => $customer->getDob(),
                'groupId'  => $customer->getGroupId(),
                'billingPostcode' => $customer->getBillingPostCode(),
                'billingCity'     => $customer->getBillingCity(),
                'billingRegion'   => $customer->getBillingRegion(),
                'billingCountryCode' => $customer->getBillingCountryCode(),
            );
        }

        $this->sendCsvResponse($customers);
    }

    public function ordersAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'complete')
            ->addAttributeToSelect('*');

        $orders = array();

        foreach ($collection as $order) {
            $orders[] = array(
                'orderId'    => $order->getId(),
                'customerId' => $order->getCustomerId(),
                'createdAt'  => $order->getCreatedAt(),
                'storeId'    => $order->getStoreId(),
                'baseDiscountAmount' => $order->getBaseDiscountAmount(),
                'baseShippingAmount' => $order->getBaseShippingAmount(),
                'baseShippingTaxAmount' => $order->getBaseShippingTaxAmount(),
                'baseTaxAmount'    => $order->getBaseTaxAmount(),
                'baseGrandTotal'   => $order->getBaseGrandTotal(),
                'baseCurrencyCode' => $order->getBaseCurrencyCode(),
                'totalItemCount'   => $order->getTotalItemCount(),
            );
        }

        $this->sendCsvResponse($orders);
    }

    public function orderItemsAction()
    {
        $collection = Mage::getModel('sales/order_item')->getCollection()
            ->addAttributeToSelect('*');

        $items = array();
        
        foreach ($collection as $item) {
            $items[] = array(
                'orderItemId' => $item->getId(),
                'orderId'      => $item->getOrderId(),
                'createdAt'    => $item->getCreatedAt(),
                'sku' => $item->getSku(),
                'productId' => $item->getProductId(),
                'basePrice' => $item->getBasePrice(),
            );
        }

        $this->sendCsvResponse($items);
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
