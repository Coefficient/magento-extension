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

}

?>
