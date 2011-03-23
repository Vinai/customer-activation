<?php


$this->startSetup();

$this->addAttribute('customer', 'customer_activated', array(
	'type' => 'int',
	'input' => 'text',
	'label' => 'Is activated',
	'global' => 1,
	'visible' => 1,
	'required' => 0,
	'user_defined' => 1,
	'default' => '0',
	'visible_on_front' => 0,
));

$this->endSetup();

// EOF

