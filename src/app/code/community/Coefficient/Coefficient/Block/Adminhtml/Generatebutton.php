<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */

class Coefficient_Coefficient_Block_Adminhtml_Generatebutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('coefficient/generate_button.phtml');
        }
        return $this;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $data = $element->getOriginalData();
        $this->addData(array('button_label' => $data['button_label'],
                             'html_id' => $element->getHtmlId()));
        return $this->_toHtml();
    }

    protected function getPostUrl()
    {
        return Mage::helper('adminhtml')->getUrl(
            'coefficient/adminhtml_coefficient/generateApiKey');
    }

    protected function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }
}

?>
