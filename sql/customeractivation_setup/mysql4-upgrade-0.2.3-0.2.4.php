<?php

$this->startSetup();

$this->updateAttribute('customer', 'customer_activated', 'frontend_input', 'select');
$this->updateAttribute('customer', 'customer_activated', 'source_model', 'eav/entity_attribute_source_boolean');

$this->endSetup();