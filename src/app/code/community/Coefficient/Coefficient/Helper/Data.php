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

    public function log($message)
    {
        Mage::log($message, null, 'coefficient.log');
    }
}

?>
