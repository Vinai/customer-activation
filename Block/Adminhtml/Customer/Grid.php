<?php

class Netzarbeiter_CustomerActivation_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
	public function setCollection($collection)
	{
		if(!Mage::getStoreConfig('customer/customeractivation/disable_ext', Mage::app() -> getStore() -> getStoreId()))
		{
			$collection -> addAttributeToSelect('customer_activated');
		}

		parent::setCollection($collection);
	}

	public function addColumn($name, $params)
	{
		if(!Mage::getStoreConfig('customer/customeractivation/disable_ext', Mage::app() -> getStore() -> getStoreId()))
		{
			if($name == 'action')
			{
				self::addColumn('customer_activated', array(
					'header'    => Mage::helper('customer') -> __('Customer Activated'),
					'align'     => 'center',
					'width'     => '80px',
					'type'      => 'options',
					'options'   => array(
						0	=> 'No',
						1	=> 'Yes'
					),
					'default'   => '0',
					'index'     => 'customer_activated'
				));
			}
		}

		parent::addColumn($name, $params);
	}

	protected function _prepareMassaction()
	{
		parent::_prepareMassaction();

		if(!Mage::getStoreConfig('customer/customeractivation/disable_ext', Mage::app() -> getStore() -> getStoreId()))
		{
			$this -> getMassactionBlock() -> addItem('customer_activated', array(
				'label'    => Mage::helper('customer') -> __('Customer Activated'),
				'url'      => $this->getUrl('customeractivation/admin/massActivation'),
				'additional' => array(
					'status' => array(
					'name' => 'customer_activated',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('customer') -> __('Customer Activated'),
					'values' => array(
						1 => 'Yes',
						0 => 'No'
					)
				))
			));
		}
	}
}