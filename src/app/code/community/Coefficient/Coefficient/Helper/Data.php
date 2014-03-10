<?php

class Coefficient_Coefficient_Helper_Data extends Mage_Core_Helper_Abstract
{
    const APIKEY_CONFIG_KEY = 'coefficient/api/apikey';
    
    public function getFoo($arg)
    {
    
    }

    public function getApiKey()
    {
        return getStoreConfig(self::APIKEY_CONFIG_KEY, 0);
    }

    public function generateApiKey()
    {
        $apiKey = md5(microtime().rand());
        Mage::getModel('core/config')->saveConfig(self::APIKEY_CONFIG_KEY, $apiKey, 'default');
        return $apiKey;
    }
}
