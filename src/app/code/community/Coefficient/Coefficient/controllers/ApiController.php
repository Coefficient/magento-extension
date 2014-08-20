<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */

/**
 * Provide an API for integration with Coefficient.
 */
class Coefficient_Coefficient_ApiController extends Mage_Core_Controller_Front_Action
{
    const DIGEST_REALM = 'Private';

    private function log($message)
    {
        Mage::log($message, null, 'coefficient.log');
    }

    private function helper()
    {
        return Mage::helper('coefficient');
    }

    /**
     * Authenticate the request using Digest auth.
     *
     * Taken from http://php.net/manual/en/features.http-auth.php
     */
    private function authenticate()
    {
        # We send Digest headers directly.
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.self::DIGEST_REALM.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5(self::DIGEST_REALM).'"');

            die('Not authenticated');
        }

        $data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST']);
        if (!$data) {
            return false;
        }

        $secret = $this->helper()->getSecret();

        // generate the valid response
        $A1 = md5($data['username'] . ':' . self::DIGEST_REALM . ':' . $secret);
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        if ($valid_response == $data['response']) {
            return $data['username'];
        } else {
            $this->log("{$_SERVER['REMOTE_ADDR']} did not generate a valid signature: sending HTTP 401");
            return false;
        }
    }

    private function isAuthorized()
    {
        $response = $this->getResponse();

        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
            $response->setHeader('HTTP/1.0', '403 Forbidden');
            $this->log("The request isn't using HTTPS");
            return false;
        }

        $apiKey = $this->authenticate();

        if ($apiKey === false) {
            $response->setHeader('HTTP/1.0', '403 Forbidden');
            $this->log("{$_SERVER['REMOTE_ADDR']} is not authenticated");
            return false;
        }

        if (!Mage::getStoreConfig('coefficient/api/enabled')) {
            $response->setHeader('HTTP/1.0', '403 Forbidden');
            $this->log("API access isn't enabled");
            return false;
        }

        return true;
    }

    public function versionAction()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }
        $version = $this->helper()->getExtensionVersion();
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

        $collection = $this->filter($collection);

        $customers = array();

        foreach ($collection as $customer) {
            $customers[] = array(
                'customerId' => $customer->getId(),
                'createdAt'  => $this->helper()->fromIsoDate($customer->getCreatedAt()),
                'updatedAt'  => $this->helper()->fromIsoDate($customer->getUpdatedAt()),
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

        $collection = $this->filter($collection);

        $products = array();

        foreach ($collection as $product) {
            $products[] = array(
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'sku'  => $product->getSku(),
                'created_at' => $this->helper()->fromIsoDate($product->getCreatedAt()),
                'updated_at' => $this->helper()->fromIsoDate($product->getUpdatedAt()),
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

        $collection = $this->filter($collection);

        $orders = array();

        foreach ($collection as $order) {
            $orders[] = array(
                'orderId'    => $order->getId(),
                'customerId' => $order->getCustomerId(),
                'createdAt'  => $this->helper()->fromIsoDate($order->getCreatedAt()),
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

        $collection = $this->filter($collection);

        $items = array();

        foreach ($collection as $item) {
            // FIXME: figure out how to do this in the collection load.
            // http://magento.stackexchange.com/questions/16824/how-to-attach-order-status-to-order-item-collection
            $orderItem = Mage::getModel('sales/order')->load($item->getOrderId());
            if ($orderItem->getStatus() != 'complete') {
                continue;
            }
            $items[] = array(
                'orderItemId' => $item->getId(),
                'orderId'     => $item->getOrderId(),
                'createdAt'   => $this->helper()->fromIsoDate($item->getCreatedAt()),
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
     * Apply any filters to the collection.
     *
     * Also applies a limit and offset if pageNum or pageSize are present in the request.
     */
    private function filter($collection)
    {
        $since = $this->getRequest()->getParam('since');
        if ($since) {
            $date = $this->helper()->fromIsoDate($since);
            $collection->addFieldToFilter('updated_at', array('gteq' => $date));
        }
    
        $pageNum = $this->getRequest()->getParam('pageNum', 1);
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

    /**
     * Function to parse the http auth header.
     * Taken from http://php.net/manual/en/features.http-auth.php
     */
    function httpDigestParse($txt)
    {
        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));
    
        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
    
        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }
    
        return $needed_parts ? false : $data;
    }

}

?>
