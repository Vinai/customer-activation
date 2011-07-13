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

class Netzarbeiter_CustomerActivation_Model_Observer extends Mage_Core_Model_Abstract
{
	const XML_PATH_MODULE_DISABLED = 'customer/customeractivation/disable_ext';

	/**
	 * Fired on customer_login event
	 * Check if the customer has been activated (via adminhtml)
	 * If not, through login error
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerLogin($observer)
	{
		if (Mage::getStoreConfig(self::XML_PATH_MODULE_DISABLED))
		{
			return;
		}

		if ($this->_isApiRequest())
		{
			return;
		}

		$customer = $observer->getEvent()->getCustomer();
		$session = Mage::getSingleton('customer/session');

		if (! $customer->getCustomerActivated())
		{
			/*
			 * Fake the old logout() method without deleting the session and all messages
			 */
			$session->setCustomer(Mage::getModel('customer/customer'))->setId(null);

			if ($this->_checkRequestRoute('customer', 'account', 'createpost'))
			{
				/*
				 * If this is a regular registration, simply display message
				 */
				$message = Mage::helper('customeractivation')->__('Please wait for your account to be activated');

				$session->addSuccess($message);
			}
			elseif ($this->_checkRequestRoute('checkout', 'onepage', 'saveorder'))
			{
				/*
				 * If this is a checkout registration, abort the checkout and
				 * redirect to login page
				 */
				$message = Mage::helper('customeractivation')->__(
					'Please wait for your account to be activated, then log in and continue with the checkout'
				);

				Mage::getSingleton('core/session')->addSuccess($message);

				$result = array(
					'redirect' => Mage::getUrl('customer/account/login')
				);
				Mage::app()->getResponse()
							->setBody(Mage::helper('core')->jsonEncode($result))
							->sendResponse();
				/* ugly, but we need to stop the further order processing */
				exit();
			}
			else
			{
				/*
				 * All other types of login
				 */
				Mage::throwException(Mage::helper('customeractivation')->__('This account is not activated.'));
			}
		}
	}

	/**
	 * Flag new accounts as such
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerSaveBefore($observer)
	{
		$customer = $observer->getEvent()->getCustomer();

		$storeId = Mage::helper('customeractivation')->getCustomerStoreId($customer);

		if (Mage::getStoreConfig(self::XML_PATH_MODULE_DISABLED, $storeId))
		{
			return;
		}

		if (!$customer->getId())
		{
			$customer->setCustomerActivationNewAccount(true);
		}
	}

	/**
	 * Send out emails
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerSaveAfter($observer)
	{
		$customer = $observer->getEvent()->getCustomer();

		$storeId = Mage::helper('customeractivation')->getCustomerStoreId($customer);

		if (Mage::getStoreConfig(self::XML_PATH_MODULE_DISABLED, $storeId))
		{
			return;
		}

		try
		{
			if (Mage::app()->getStore()->isAdmin())
			{
				if (!$customer->getOrigData('customer_activated') && $customer->getCustomerActivated())
				{
					Mage::helper('customeractivation')->sendCustomerNotificationEmail($customer);
				}
			}
			else
			{
				if ($customer->getCustomerActivationNewAccount())
				{
					Mage::helper('customeractivation')->sendAdminNotificationEmail($customer);
				}
				$customer->setCustomerActivationNewAccount(false);
			}
		}
		catch (Exception $e)
		{
			Mage::throwException($e->getMessage());
		}
	}

	/**
	 * Return true if the reqest is made via the api
	 *
	 * @return boolean
	 */
	protected function _isApiRequest()
	{
		return Mage::app()->getRequest()->getModuleName() === 'api';
	}

	/**
	 * Check the current module, controller and action against the given values.
	 *
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return bool
	 */
	protected function _checkRequestRoute($module, $controller, $action)
	{
		$req = Mage::app()->getRequest();
		if (strtolower($req->getModuleName()) == $module
			&& strtolower($req->getControllerName()) == $controller
			&& strtolower($req->getActionName()) == $action)
		{
			return true;
		}
		return false;
	}
}
