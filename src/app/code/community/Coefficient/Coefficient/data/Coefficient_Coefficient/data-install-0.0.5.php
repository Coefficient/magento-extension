<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */
/* @var $this Mage_Core_Model_Resource_Setup */
$key = Mage::helper('coefficient')->generateApiKey();
error_log("setup generated api key $key");
Mage::app()->getCacheInstance()->invalidateType('config');
