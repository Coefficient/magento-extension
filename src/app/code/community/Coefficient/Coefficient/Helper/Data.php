<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */

class Coefficient_Coefficient_Helper_Data extends Mage_Core_Helper_Abstract
{
    const APIKEY_CONFIG_KEY = 'coefficient/api/apikey';
    const SECRET_CONFIG_KEY = 'coefficient/api/secret';
    
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::APIKEY_CONFIG_KEY, 0);
    }

    public function getSecret()
    {
        return Mage::getStoreConfig(self::SECRET_CONFIG_KEY, 0);
    }

    public function generateApiKey()
    {
        $apiKey = md5(microtime() . rand());
        Mage::getModel('core/config')->saveConfig(self::APIKEY_CONFIG_KEY, $apiKey, 'default');
        return $apiKey;
    }

    public function generateSecret()
    {
        $secret = md5(microtime() . rand());
        Mage::getModel('core/config')->saveConfig(self::SECRET_CONFIG_KEY, $secret, 'default');
        return $secret;
    }

    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Coefficient_Coefficient->version;
    }

    /**
     * Convert timestamps to ISO 8601 format.
     *
     * Timestamps are stored as UTC but when accessed through Magento they
     * do not include any offset information.
     */
    public function toIsoDate($dateString)
    {
        $date = new DateTime($dateString, new DateTimeZone('UTC'));
        return $date->format('c');
    }

    /**
     * Convert an ISO 8601 formatted date to a MySQL TIMESTAMP format.
     */
    public function fromIsoDate($dateString)
    {
        return date('Y-m-d H:i:s', strtotime($dateString));
    }

}

?>
