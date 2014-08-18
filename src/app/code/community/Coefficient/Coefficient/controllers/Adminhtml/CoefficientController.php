<?php
/**
 * Copyright (c) 2014 Coefficient, Inc.
 *
 * This file is part of the Coefficient extension for Magento and is released
 * under the MIT License. For full copyright and license information, please
 * see the LICENSE file.
 */

class Coefficient_Coefficient_Adminhtml_CoefficientController extends Mage_Adminhtml_Controller_Action
{

    public function generateSecretAction()
    {
        if ($this->getRequest()->isPost()) {
            $helper = Mage::helper('coefficient');

            $helper->generateSecret();

            $message = 'Generated a new secret! Remember to update Coefficient with this new value.';

            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
        $this->_redirect('adminhtml/system_config/edit/section/coefficient');
    }

}

?>
