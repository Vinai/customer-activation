<?php

/**
 * @see Netzarbeiter_CustomerActivation_AdminController
 *
 * @loadSharedFixture global.yaml
 * @doNotIndexAll
 */
class Netzarbeiter_CustomerActivation_Test_Controller_Adminhtml_AdminControllerTest
    extends Netzarbeiter_CustomerActivation_Test_Controller_Adminhtml_AbstractController
{
    /**
     * Force a customer customer_activation attribute to be a specific value (in case of null remove record)
     *
     * This hack is needed because the eav fixture generator does not set null values.
     *
     * @param int $customerId
     * @see EcomDev_PHPUnit_Model_Mysql4_Fixture_Eav_Abstract::_getAttributeRecords()
     */
    protected function _initCustomer($customerId)
    {
        $value = $this->expected("initial-status-%s", $customerId)->getCustomerActivated();

        /** @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute */
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'customer_activated');
        /** @var Varien_Db_Adapter_Pdo_Mysql $con */
        $con = Mage::getSingleton('core/resource')->getConnection('customer_write');
        $table = $attribute->getBackend()->getTable();

        $where = array();
        $where[] = $con->quoteInto('entity_id = ?', $customerId);
        $where[] = $con->quoteInto('attribute_id = ?', $attribute->getId());

        if (is_null($value)) {
            $con->delete($table, implode(' AND ', $where));

        } else {
            $select = $con->select()->from($table, new Zend_Db_Expr('COUNT(*)'))
                ->where('entity_id = ?', $customerId)
                ->where('attribute_id = ?', $attribute->getId());
            $exists = $con->fetchOne($select);

            if ($exists) {
                $con->update($table, array('value' => $value), implode(' AND ', $where));

            } else {
                $con->insert($table, array(
                    'entity_id' => $customerId,
                    'entity_type_id' => $attribute->getEntity()->getId(),
                    'attribute_id' => $attribute->getId(),
                    'value' => $value
                ));
            }
        }
    }

    /**
     * Check the passed customer ID's match the expected initial activation status
     *
     * @param $testCustomerIds
     */
    public function assertInitialStatus($testCustomerIds)
    {
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $testCustomerIds))
            ->addAttributeToSelect('customer_activated');

        foreach ($testCustomerIds as $customerId) {
            $customer = $customers->getItemById($customerId);
            $expectedStatus = $this->expected("initial-status-%s", $customerId)->getCustomerActivated();
            $realStatus = $customer->getData('customer_activated');
            $message = sprintf("Expected customer %d to initially be %s but found to be %s",
                $customer->getId(),
                ($expectedStatus ? 'activated' : 'deactivated'),
                ($realStatus ? 'activated' : 'deactivated')
            );

            $this->assertEquals($expectedStatus, $realStatus, $message);
        }
    }

    /**
     * @param array $testCustomerIds
     * @param array $postCustomerIds
     * @param int $status Activate or deactivate customers
     *
     * @test
     * @loadFixture customers.yaml
     * @dataProvider dataProvider
     */
    public function massActivation($testCustomerIds, $postCustomerIds, $status)
    {
        // Hack to work around fixture limitation:
        // Force customer 3 initial value not to be set according to fixture file
        $this->_initCustomer(3);

        $this->assertInitialStatus($testCustomerIds);

        $params = array(
            '_store' => 'admin',
            '_query' => array(
                'customer' => $postCustomerIds,
                'customer_activated' => (int) $status
            )
        );

        $this->dispatch('customeractivation/admin/massActivation', $params);

        $expectations = $this->expected(
            "%s-%s-%d", implode(',', $testCustomerIds), implode(',', $postCustomerIds), $status
        );

        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $testCustomerIds))
            ->addAttributeToSelect('customer_activated');

        foreach ($testCustomerIds as $customerId) {
            $customer = $customers->getItemById($customerId);
            $expectedStatus = $expectations->getData('customer' . $customerId);
            $realStatus = $customer->getData('customer_activated');
            $message = sprintf("Expected customer %d to be %s but found to be %s",
                $customer->getId(),
                ($expectedStatus ? 'activated' : 'deactivated'),
                ($realStatus ? 'activated' : 'deactivated')
            );

            $this->assertEquals($expectedStatus, $realStatus, $message);
        }
    }
}