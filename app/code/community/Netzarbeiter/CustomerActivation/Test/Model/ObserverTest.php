<?php
/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_CustomerActivation
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @see Netzarbeiter_CustomerActivation_Model_Observer
 *
 * @loadSharedFixture global.yaml
 * @doNotIndexAll
 */
class Netzarbeiter_CustomerActivation_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $_model;

    /**
     * Enable global events
     */
    protected function setUp()
    {
        $this->app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
    }

    /**
     * Reset current store to admin and clean up created customer entity
     */
    protected function tearDown()
    {
        $this->setCurrentStore('admin');
        if ($this->_model && $this->_model->getId()) {
            $this->_model->delete();
        }
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function newCustomerActivationState($email, $storeCode, $groupCode, $activeByDefault, $specificGroups)
    {
        $store = $this->app()->getStore($storeCode);
        $store->setConfig(Netzarbeiter_CustomerActivation_Helper_Data::XML_PATH_DEFAULT_STATUS, $activeByDefault);
        $store->setConfig(Netzarbeiter_CustomerActivation_Helper_Data::XML_PATH_DEFAULT_STATUS_BY_GROUP, $specificGroups);
        $this->setCurrentStore($store);

        /* @var $group Mage_Customer_Model_Group */
        $group = Mage::getModel('customer/group')->load($groupCode, 'customer_group_code');
        $this->_model = Mage::getModel('customer/customer');
        $this->_model->setData(array(
            'store_id' => $store->getId(),
            'website_id' => $store->getWebsiteId(),
            'group_id' => $group->getId(),
            'email' => $email,
        ))->save();

        // Since it's so easy thanks to EcomDev_PHPUnit, lets check triggered events, too
        $this->assertEventDispatchedExactly('customer_save_before', 1);
        $this->assertEventDispatchedExactly('customer_save_after', 1);

        $expected = $this->expected("%s-%d-%d-%d", $storeCode, $group->getId(), $activeByDefault, $specificGroups)
            ->getIsActivated();

        $message = sprintf(
            "Expected new customer %s with group %s in store %s to be %s, but found to be %s\n" .
                "All groups default status: %s, %srequire activation for specific groups)",
            $this->_model->getEmail(), $group->getCode(), $store->getCode(),
            ($expected ? 'activated' : 'inactive'),
            ($expected ? 'inactive' : 'active'),
            ($activeByDefault ? 'active' : 'inactive'),
            ($specificGroups ? '' : "don't ")
        );
        $this->assertEquals($expected, $this->_model->getCustomerActivated(), $message);
    }
}