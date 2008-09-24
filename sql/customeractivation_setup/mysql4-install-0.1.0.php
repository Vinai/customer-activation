<?php

$attribute_code = 'customer_activated';

// load id for customer entity
$read = Mage::getSingleton('core/resource')->getConnection('core_read');
$eid = $read->fetchRow("select entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code = 'customer'");
$customer_type_id = $eid['entity_type_id'];


$attr = array(
	'type' => 'int',
	'input' => 'text',
	'label' => 'Is activated',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 1,
	'default' => '0',
	'visible_on_front' => 0,
);


$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute($customer_type_id, $attribute_code, $attr);

$installer->endSetup();

// EOF

