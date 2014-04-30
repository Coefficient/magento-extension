<?php

class Coefficient_Coefficient_Helper_Data extends Mage_Core_Helper_Abstract
{
    const APIKEY_CONFIG_KEY = 'coefficient/api/apikey';
    
    public function getApiKey()
    {
        return Mage::getStoreConfig(self::APIKEY_CONFIG_KEY, 0);
    }

    public function generateApiKey()
    {
        $apiKey = md5(microtime().rand());
        Mage::getModel('core/config')->saveConfig(self::APIKEY_CONFIG_KEY, $apiKey, 'default');
        return $apiKey;
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
    public function utcDate($dateString)
    {
        $date = new DateTime($dateString, new DateTimeZone('UTC'));
        return $date->format('c');
    }
}

?>
