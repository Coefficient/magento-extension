<?php

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
                             'html_id' => $element->getHtmlId(),
                             'url' => '#'));
        return $this->_toHtml();
    }
}

?>
