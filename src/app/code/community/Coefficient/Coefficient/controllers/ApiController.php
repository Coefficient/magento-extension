<?php

/**
 * Provide an API for integration with Coefficient.
 */
class Coefficient_Coefficient_ApiController extends Mage_Core_Controller_Front_Action
{

    private function log($message)
    {
        Mage::log($message, null, 'coefficient.log');
    }

    private function notAuthorized()
    {
        $this->log("{$_SERVER['REMOTE_ADDR']} not authorized: sending HTTP 403");

        $this->getResponse()
            ->setHttpResponseCode(403)
            ->setBody('Not Authorized');
    }

    private function getRequestApiKey()
    {
        $auth_header = $this->getRequest()->getHeader('Authorization');

        $matches = array();
        preg_match('/token apiKey="(.+)"/', $auth_header, $matches);

        if (isset($matches[1])) {
            return trim($matches[1]);
        }
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

        $collection = $this->limit($collection);

        $customers = array();

        foreach ($collection as $customer) {
            $customers[] = array(
                'customerId' => $customer->getId(),
                'createdAt'  => $customer->getCreatedAt(),
                'email' => $customer->getEmail(),
                'name'  => $customer->getName(),
                'firstname' => $customer->getFirstname(),
                'lastname'  => $customer->getLastname(),
                //'gender'    => $customer->getAttributeText('gender'),
                /* TODO: evalulate how efficient this is. 
                 I'm not sure if this is the best way to load the customer's
                 gender text (note: getAttributeText('gender') returns nothing.
                 I suspect I need to add an additional attribute to the join. */
                'gender'   => $customer->getAttribute('gender')->getSource()->getOptionText($customer->getGender()),
                'dob'      => date('Y-m-d', strtotime($customer->getDob())),
                'groupId'  => $customer->getGroupId(),
                'billingPostCode' => $customer->getBillingPostCode(),
                'billingCity'     => $customer->getBillingCity(),
                'billingRegion'   => $customer->getBillingRegion(),
                'billingCountryCode' => $customer->getBillingCountryCode(),
            );
        }

        $this->sendCsvResponse($customers);
    }

    public function productsAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }
        
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('cost');

        $collection = $this->limit($collection);

        $products = array();

        foreach ($collection as $product) {
            $products[] = array(
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'sku'  => $product->getSku(),
                'created_at' => $product->getCreatedAt(),
                'updated_at' => $product->getUpdatedAt(),
                'price' => $product->getPrice(),
                'cost'  => $product->getCost(),
                'is_salable' => $product->getIsSalable(),
            );
        }

        $this->sendCsvResponse($products);
    }

    public function ordersAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', 'complete')
            ->addAttributeToSelect('*');

        $collection = $this->limit($collection);

        $orders = array();

        foreach ($collection as $order) {
            $orders[] = array(
                'orderId'    => $order->getId(),
                'customerId' => $order->getCustomerId(),
                'createdAt'  => $order->getCreatedAt(),
                'storeId'    => $order->getStoreId(),
                'totalItemCount' => $order->getTotalItemCount(),
                'baseGrandTotal' => $order->getBaseGrandTotal(),
                'baseSubtotalInclTax'   => $order->getBaseSubtotalInclTax(),
                'baseDiscountAmount'    => $order->getBaseDiscountAmount(),
                'baseShippingAmount'    => $order->getBaseShippingAmount(),
                'baseShippingTaxAmount' => $order->getBaseShippingTaxAmount(),
                'baseTaxAmount'    => $order->getBaseTaxAmount(),
                'baseCurrencyCode' => $order->getBaseCurrencyCode(),
            );
        }

        $this->sendCsvResponse($orders);
    }

    public function orderItemsAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }

        $collection = Mage::getResourceModel('sales/order_item_collection')
            ->addAttributeToSelect('*');

        $collection = $this->limit($collection);

        $items = array();
        
        foreach ($collection as $item) {
            // FIXME: figure out how to do this in the collection load.
            // http://magento.stackexchange.com/questions/16824/how-to-attach-order-status-to-order-item-collection
            $orderItem = Mage::getModel('sales/order')->load($item->getId());
            if ($orderItem->getStatus() != 'complete') {
                continue;
            }
            $items[] = array(
                'orderItemId' => $item->getId(),
                'orderId'     => $item->getOrderId(),
                'createdAt'   => $item->getCreatedAt(),
                'productId'   => $item->getProductId(),
                'qtyOrdered'  => $item->getQtyOrdered(),
                'basePrice'   => $item->getBasePrice(),
                'baseOriginalPrice' => $item->getBaseOriginalPrice(),
                'basePriceInclTax'  => $item->getBasePriceInclTax(),
            );
        }

        $this->sendCsvResponse($items);
    }

    /**
     * Apply a limit to the collection.
     */
    private function limit($collection)
    {
        $pageNum = $this->getRequest()->getParam('pageNum');
        $pageSize = $this->getRequest()->getParam('pageSize', 500);

        $collection->setCurPage($pageNum);
        $collection->setPageSize($pageSize);

        if ($pageNum > $collection->getLastPageNumber()) {
            // Magento massages curPage to be <= the total number of available
            // pages, so return an empty array if we've actually exceeded this.
            return array();
        }

        return $collection;
    }

    /**
     * Send a CSV response directly to the outut stream.
     *
     * Writing CSV content bypasses Magento's Response object.
     */
    private function sendCsvResponse($rows)
    {
        $response = $this->getResponse();
        $response->setHeader('Content-type', 'text/csv', true);

        if ($rows) {
            $this->writeCsv($rows);
        } else {
            $response->setHttpResponseCode(204)->setBody('No Content');
        }
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
}

?>
