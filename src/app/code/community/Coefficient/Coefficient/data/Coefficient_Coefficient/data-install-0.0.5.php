<?php
error_log("setup script...");
/* @var $this Mage_Core_Model_Resource_Setup */
$key = Mage::helper('coefficient')->generateApiKey();
error_log($key);
Mage::app()->getCacheInstance()->invalidateType('config');
