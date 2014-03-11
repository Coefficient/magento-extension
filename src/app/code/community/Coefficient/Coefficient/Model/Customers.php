<?php

class Coefficient_Coefficient_Model_Customers
{
    public $customers = array();

    public function loadCustomers()
    {
        /* This might be inefficient but it's got to be better than loading
           individual models in a loop.
         */
        $collection = Mage::getResourceModel('customer/customer_collection')
               ->addNameToSelect()
               ->addAttributeToSelect('firstname')
               ->addAttributeToSelect('lastname')
               ->addAttributeToSelect('email')
               ->addAttributeToSelect('created_at')
               ->addAttributeToSelect('group_id')
               ->joinAttribute('billing_street', 'customer_address/street', 'default_billing', null, 'left')
               ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
               ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
               ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
               ->joinAttribute('billing_country_code', 'customer_address/country_id', 'default_billing', null, 'left');

        foreach ($collection as $customer) {
            $data = $customer->getData();
            $this->customers[] = array(
                'customer_id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'name' => $customer->getName(),
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
            );
        }

        return $this;
    }
}

?>
