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
 * copyright  Copyright (c) 2012 Vinai Kopp http://netzarbeiter.com/
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_CustomerActivation_AdminController extends Mage_Adminhtml_Controller_Action
{
	public function massActivationAction()
	{
		$customerIds = $this->getRequest()->getParam('customer');

		if(! is_array($customerIds))
		{
			Mage::getSingleton('adminhtml/session')->addError(
				Mage::helper('customeractivation')->__('Please select item(s)')
			);
		}
		else
		{
			$activationStatus = $this->getRequest()->getParam('customer_activated');
			try
			{
				foreach ($customerIds as $customerId)
				{
					/** @var $model Mage_Customer_Model_Customer */
					$model = Mage::getModel('customer/customer')->load($customerId);
					$model->setCustomerActivated($activationStatus)->save();
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('customeractivation')->__(
						'Total of %d record(s) were successfully saved', count($customerIds)
					)
				);
			}
			catch(Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('adminhtml/customer');
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
	}
}