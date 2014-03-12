<?php

class Coefficient_Coefficient_Adminhtml_CoefficientController extends Mage_Adminhtml_Controller_Action
{

    public function generateApiKeyAction()
    {
        if ($this->getRequest()->isPost()) {
            $helper = Mage::helper('coefficient');

            $helper->generateApiKey();
            $helper->log('user generated a new API key');

            $message = 'Generated a new API key! Remember to update Coefficient with this new key.';

            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
        $this->_redirect('adminhtml/system_config/edit/section/coefficient');
    }

}

?>
