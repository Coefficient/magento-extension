<?php

class Coefficient_Coefficient_Model_Customer
{
    public function parseCustomer($customer)
    {
        $this->customer_id = $customer->getId();
        $this->email = $customer->getData('email');
        $this->name = $customer->getName();
        $this->first_name = $customer->getFirstName();
        $this->last_name = $customer->getLastName();
        $this->birthday = $customer->getDob();
        $this->gender = $customer->getGender();
        $this->banana = $customer->getBanana();
        $this->city = $customer->getCity();

        return $this;
    }
}

?>
