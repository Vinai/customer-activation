<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * package    Netzarbeiter_CustomerActivation
 * copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com/
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

/*
 * Add required information for Magento 1.4.2 and newer
 */
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'customer_activated')
            ->setData('used_in_forms', array('adminhtml_customer'))
            ->save();
}

$installer->endSetup();
