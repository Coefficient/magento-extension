<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$key = Mage::helper('coefficient')->generateApiKey();
error_log("setup generated api key $key");
Mage::app()->getCacheInstance()->invalidateType('config');
