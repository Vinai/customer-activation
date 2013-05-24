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
 * copyright  Copyright (c) 2013 Vinai Kopp http://netzarbeiter.com/
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_CustomerActivation_Model_Resource_Customer
    extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Emulate customer resource model for easy access
     */
    protected function _construct()
    {
        $this->setType('customer');
        $this->setConnection('customer_read', 'customer_write');
        return parent::_construct();
    }

    /**
     * Attempt to quickly set the specified customer activation status
     *
     * @param array $customerIds
     * @param int $value
     * @return $this
     */
    public function massSetActivationStatus(array $customerIds, $value)
    {
        $customerIds = $this->_getValidCustomerIds($customerIds);
        if ($customerIds) {
            $attribute = $this->getAttribute('customer_activated');
            $table = $attribute->getBackend()->getTable();
            $select = $this->_getReadAdapter()->select()
                ->from($table, 'entity_id')
                ->where('entity_id IN (?)', $customerIds)
                ->where('attribute_id = ?', $attribute->getId());

            $updateIds = $this->_getReadAdapter()->fetchCol($select);
            $insertIds = array_diff($customerIds, $updateIds);

            if ($updateIds) {
                $cond = $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $updateIds);
                $this->_getWriteAdapter()->update($table, array('value' => $value), $cond);
            }
            if ($insertIds) {
                $rows = array();
                foreach ($insertIds as $customerId) {
                    $rows[] = array(
                        'entity_type_id' => $this->getEntityType()->getId(),
                        'attribute_id' => $attribute->getId(),
                        'entity_id' => $customerId,
                        'value' => $value
                    );
                }
                $this->_getWriteAdapter()->insertMultiple($table, $rows);
            }
        }
        return $this;
    }

    /**
     * Return an array containing the valid subset of the specified customer IDs
     *
     * @param array $customerIds
     * @return array
     */
    protected function _getValidCustomerIds(array $customerIds)
    {
        $column = $this->getEntityIdField();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), $column)
            ->where($column . ' IN (?)', $customerIds);
        return $this->_getReadAdapter()->fetchCol($select);
    }
}