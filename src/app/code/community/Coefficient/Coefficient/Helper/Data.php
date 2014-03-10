<?php

class Coefficient_Coefficient_Helper_Data extends Mage_Core_Helper_Abstract
{
    const APIKEY_CONFIG_KEY = 'coefficient/api/apikey';
    
    public function getFoo($arg)
    {
    
    }

    public function getApiKey()
    {
        $apiKey = getStoreConfig(self::APIKEY_CONFIG_KEY, 0);
        if (!$apiKey) {
            $this->generateApiKey();
        }
    }

    public function generateApiKey()
    {
        $apiKey = md5(microtime().rand());
        Mage::getModel('core/config')->saveConfig(self::APIKEY_CONFIG_KEY, $apiKey, 'default');
        return $apiKey;
    }
}
