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

class Netzarbeiter_CustomerActivation_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_ADMIN_NOTIFICATION = 'customer/customeractivation/admin_email';
	const XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'customer/customeractivation/registration_admin_template';
	const XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE = 'customer/customeractivation/activation_template';
	const XML_PATH_ALERT_CUSTOMER = 'customer/customeractivation/alert_customer';
	const XML_PATH_ALERT_ADMIN = 'customer/customeractivation/alert_admin';

	/**
	 * Send Admin a notification whenever a new customer account is registered
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Netzarbeiter_CustomerActivation_Helper_Data
	 */
	public function sendAdminNotificationEmail(Mage_Customer_Model_Customer $customer)
	{
		$storeId = $this->getCustomerStoreId($customer);
		if (Mage::getStoreConfig(self::XML_PATH_ALERT_ADMIN, $storeId))
		{
			$to = $this->_getEmails(self::XML_PATH_EMAIL_ADMIN_NOTIFICATION, $storeId);
			$this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE);
		}
		return $this;
	}

	/**
	 * Send Customer a notification when her account is activated
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Netzarbeiter_CustomerActivation_Helper_Data
	 */
	public function sendCustomerNotificationEmail(Mage_Customer_Model_Customer $customer)
	{
		if (Mage::getStoreConfig(self::XML_PATH_ALERT_CUSTOMER, $this->getCustomerStoreId($customer)))
		{
			$to = array(array(
				'name' => $customer->getName(),
				'email' => $customer->getEmail(),
			));
			$this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE);
		}
		return $this;
	}

	/**
	 * Send transactional email
	 *
	 * @param array|string $to
	 * @param Mage_Customer_Model_Customer $customer
	 * @param string $templateConfigPath
	 * @return Netzarbeiter_CustomerActivation_Helper_Data
	 */
	protected function _sendNotificationEmail($to, $customer, $templateConfigPath)
	{
		if (! $to) return;

		$storeId = $this->getCustomerStoreId($customer);

		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */

		$template = Mage::getStoreConfig($templateConfigPath, $storeId);

		$sendTo = array();
		foreach ($to as $recipient)
		{
			if (is_array($recipient))
			{
				$sendTo[] = $recipient;
			}
			else
			{
				$sendTo[] = array(
					'email' => $recipient,
					'name' => null,
				);
			}
		}
		
		foreach ($sendTo as $recipient) {
			$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
			->sendTransactional(
				$template,
				Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $storeId),
				$recipient['email'],
				$recipient['name'],
				array(
					'customer' => $customer,
					'shipping' => $customer->getPrimaryShippingAddress(),
					'billing' => $customer->getPrimaryBillingAddress(),
					'store' => Mage::app()->getStore($storeId),
				)
			);
		}
		
		$translate->setTranslateInline(true);

		return $this;
	}

	protected function _getEmails($configPath, $storeId = null)
	{
		$data = Mage::getStoreConfig($configPath, $storeId);
		if (!empty($data)) {
			return explode(',', $data);
		}
		return false;
	}

	/**
	 * Return the Store Id to read configuration settings for this customer from
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return int
	 */
	public function getCustomerStoreId(Mage_Customer_Model_Customer $customer)
	{
		/*
		 * Only set in Adminhtml UI
		 */
		if (! ($storeId = $customer->getSendemailStoreId()))
		{
			/*
			 * store_id might be zero if the account was created in the admin interface
			 */
			$storeId = $customer->getStoreId();
			if (! $storeId && $customer->getWebsiteId())
			{
				/*
				 * Use the default store groups store of the customers website
				 */
				if ($store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore())
				{
					$storeId = $store->getId();
				}
			}
		}
		return $storeId;
	}
}