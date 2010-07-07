<?php


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
	 * @param Mage_Customer_Model_Customer|string $to
	 * @param string $templateConfigPath
	 * @return Mage_Sales_Model_Customert
	 */
	protected function _sendNotificationEmail($to, $customer, $templateConfigPath)
	{
		Mage::log(__METHOD__);
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
			Mage::log('sending out email to ' . $recipient['email']);
			$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$customer->getStoreId()))
			->sendTransactional(
				$template,
				Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $customer->getStoreId()),
				$recipient['email'],
				$recipient['name'],
				array(
					'customer'	=> $customer,
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