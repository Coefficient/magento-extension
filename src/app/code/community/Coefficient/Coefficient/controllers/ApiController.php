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

        $helper = Mage::helper('coefficient');

        if (!$apiKey) {
            $this->notAuthorized();
            $helper->log("No API key in request authorization header.");
            return false;
        }
        
        if ($apiKey != Mage::helper('coefficient')->getApiKey()) {
            $this->notAuthorized();
            $helper->log("Incorrect API key.");
            return false;
        }
        
        if (!Mage::getStoreConfig('coefficient/api/enabled')) {
            $this->notAuthorized();
            $helper->log("API access isn't enabled.");
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

    public function versionAction()
    {
        if (!$this->authorize()) {
            return $this;
        }
        $version = Mage::helper('coefficient')->getExtensionVersion();
        $this->getResponse()->setBody($version);
    }

    public function customersAction()
    {
        #if (!$this->authorize()) {
        #    return $this;
        #}

        $collection = Mage::getResourceModel('customer/customer_collection')
               ->addNameToSelect()
               ->addAttributeToSelect('*')
               #->addAttributeToSelect('firstname')
               #->addAttributeToSelect('lastname')
               #->addAttributeToSelect('email')
               #->addAttributeToSelect('created_at')
               #->addAttributeToSelect('group_id')
               ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
               ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
               ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
               ->joinAttribute('billing_country_code', 'customer_address/country_id', 'default_billing', null, 'left');

        $customers = array();

        foreach ($collection as $customer) {
            #$data = $customer->getData();
            #print_r($data);
            $customers[] = array(
                'customer_id' => $customer->getId(),
                'created_at'  => $customer->getCreatedAt(),
                'email' => $customer->getEmail(),
                'name'  => $customer->getName(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname(),
                'gender'    => $customer->getAttributeText('gender'),
                'dob'       => $customer->getDob(),
                'group_id'  => $customer->getGroupId(),
                'billing_postcode' => $customer->getBillingPostCode(),
                'billing_city'     => $customer->getBillingCity(),
                'billing_region'   => $customer->getBillingRegion(),
                'billing_country_code' => $customer->getBillingCountryCode(),
            );
        }

        $this->sendCsvResponse($customers);
    }

    public function ordersAction()
    {
        #if (!$this->authorize()) {
        #    return $this;
        #}

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'complete')
            ->addAttributeToSelect('*');

        $orders = array();

        foreach ($collection as $order) {
            $orders[] = array(
                'order_id'    => $order->getId(),
                'customer_id' => $order->getCustomerId(),
                'created_at'  => $order->getCreatedAt(),
                'store_id'    => $order->getStoreId(),
                'base_discount_amount' => $order->getBaseDiscountAmount(),
                'base_shipping_amount' => $order->getBaseShippingAmount(),
                'base_shipping_tax_amount' => $order->getBaseShippingTaxAmount(),
                'base_tax_amount'    => $order->getBaseTaxAmount(),
                'base_grand_total'   => $order->getBaseGrandTotal(),
                'base_currency_code' => $order->getBaseCurrencyCode(),
                'total_item_count'   => $order->getTotalItemCount(),
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
                'order_item_id' => $item->getId(),
                'order_id'      => $item->getOrderId(),
                'created_at'    => $item->getCreatedAt(),
                'sku' => $item->getSku(),
                'product_id' => $item->getProductId(),
                'base_price' => $item->getBasePrice(),
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
