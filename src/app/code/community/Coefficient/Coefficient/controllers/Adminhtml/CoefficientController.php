<?php

class Coefficient_Coefficient_Adminhtml_CoefficientController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title = "Coefficient Banana yall";

        $this->loadLayout()->renderLayout();
        
        return $this;
    }

    public function helloAction()
    {
        echo "Hello, World!";
    }

    public function apikeyAction()
    {
        if ($this->getRequest()->isPost()) {
            Mage::helper('coefficient')->generateApiKey();
            $message = "Generated a new API key";
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
    }

}

?>
