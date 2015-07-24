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

$resource = Mage::getResourceModel('customer/customer');

// Set activation status for existing customers to true
$select = $installer->getConnection()->select()
    ->from($resource->getEntityTable(), $resource->getEntityIdField());
$customerIds = $installer->getConnection()->fetchCol($select);

foreach (array_chunk($customerIds,1000) as $updateIds) {
    $updatedCustomerIds = Mage::getResourceModel('customeractivation/customer')->massSetActivationStatus($updateIds, 1);
}

$installer->endSetup();
