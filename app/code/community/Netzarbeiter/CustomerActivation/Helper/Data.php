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

class Netzarbeiter_CustomerActivation_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_ADMIN_NOTIFICATION = 'customer/customeractivation/admin_email';
	const XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'customer/customeractivation/registration_admin_template';
	const XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE = 'customer/customeractivation/activation_template';

	/**
	 * Send Admin a notification whenever a new customer account is registered
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Netzarbeiter_CustomerActivation_Helper_Data
	 */
	public function sendAdminNotificationEmail(Mage_Customer_Model_Customer $customer)
	{
		$to = $this->_getEmails(self::XML_PATH_EMAIL_ADMIN_NOTIFICATION, $customer->getStoreId());
		return $this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE);
	}

	/**
	 * Send Customer a notification when her account is activated
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Netzarbeiter_CustomerActivation_Helper_Data
	 */
	public function sendCustomerNotificationEmail(Mage_Customer_Model_Customer $customer)
	{
		$to = array(array(
			'name' => $customer->getName(),
			'email' => $customer->getEmail(),
		));
		return $this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE);
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

		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */

		$template = Mage::getStoreConfig($templateConfigPath, $customer->getStoreId());

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
			$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$customer->getStoreId()))
			->sendTransactional(
				$template,
				Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $customer->getStoreId()),
				$recipient['email'],
				$recipient['name'],
				array(
					'customer' => $customer,
					'shipping' => $customer->getPrimaryShippingAddress(),
					'billing' => $customer->getPrimaryBillingAddress(),
					'store' => Mage::app()->getStore($customer->getStoreId()),
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
}