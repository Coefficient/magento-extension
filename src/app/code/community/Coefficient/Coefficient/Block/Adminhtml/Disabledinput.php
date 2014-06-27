<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */

class Coefficient_Coefficient_Block_Adminhtml_Disabledinput extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /* Note that this doesn't actually work because the "disabled"
           attribute is removed when a "depends" condition is true. */
        $element->setDisabled('disabled');
    }
}

?>
