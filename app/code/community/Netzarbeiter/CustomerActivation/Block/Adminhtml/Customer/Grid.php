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
 * copyright  Copyright (c) 2011 Vinai Kopp http://netzarbeiter.com/
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_CustomerActivation_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
	public function setCollection($collection)
	{
		if ($this->_isActive())
		{
			$collection->addAttributeToSelect('customer_activated');
		}

		return parent::setCollection($collection);
	}

	public function addColumn($name, $params)
	{
		if ($this->_isActive())
		{
			if ($name == 'action')
			{
				self::addColumn('customer_activated', array(
					'header'    => Mage::helper('customer')->__('Customer Activated'),
					'align'     => 'center',
					'width'     => '80px',
					'type'      => 'options',
					'options'   => array(
						'0' => 'No',
						'1' => 'Yes'
					),
					'default'   => '0',
					'index'     => 'customer_activated',
					'renderer'  => 'customeractivation/adminhtml_widget_grid_column_renderer_boolean'
				));
			}
		}

		return parent::addColumn($name, $params);
	}

	protected function _prepareMassaction()
	{
		parent::_prepareMassaction();

		if ($this->_isActive())
		{
			$this -> getMassactionBlock() -> addItem('customer_activated', array(
				'label'   => Mage::helper('customer') -> __('Customer Activated'),
				'url'     => $this->getUrl('customeractivation/admin/massActivation'),
				'additional' => array(
					'status' => array(
					'name'   => 'customer_activated',
					'type'   => 'select',
					'class'  => 'required-entry',
					'label'  => Mage::helper('customer') -> __('Customer Activated'),
					'values' => array(
						'1' => 'Yes',
						'0' => 'No'
					)
				))
			));
		}

		return $this;
	}

	protected function _isActive()
	{
		if (Mage::getStoreConfig('customer/customeractivation/disable_ext') &&
			! Mage::getStoreConfig('customer/customeractivation/always_active_in_admin')
		)
		{
			return false;
		}
		return true;
	}
}
