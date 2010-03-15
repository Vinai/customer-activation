<?php

$this->startSetup();

$customer = Mage::getModel('customer/customer');
$attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();

$this->addAttributeToSet('customer', $attrSetId, 'General', 'customer_activated');

$this->endSetup();

