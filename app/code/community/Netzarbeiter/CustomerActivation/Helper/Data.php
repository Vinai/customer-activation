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
 * copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com/
 * license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_CustomerActivation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_EMAIL_ADMIN_NOTIFICATION = 'customer/customeractivation/admin_email';
    const XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'customer/customeractivation/registration_admin_template';
    const XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE = 'customer/customeractivation/activation_template';
    const XML_PATH_ALERT_CUSTOMER = 'customer/customeractivation/alert_customer';
    const XML_PATH_ALERT_ADMIN = 'customer/customeractivation/alert_admin';
    const XML_PATH_DEFAULT_STATUS = 'customer/customeractivation/activation_status_default';
    const XML_PATH_DEFAULT_STATUS_BY_GROUP = 'customer/customeractivation/require_activation_for_specific_groups';
    const XML_PATH_DEFAULT_STATUS_GROUPS = 'customer/customeractivation/require_activation_groups';

    const XML_PATH_MODULE_DISABLED = 'customer/customeractivation/disable_ext';
    const XML_PATH_ALWAYS_ACTIVE_ADMIN = 'customer/customeractivation/always_active_in_admin';

    const STATUS_ACTIVATE_WITHOUT_EMAIL = 1;
    const STATUS_ACTIVATE_WITH_EMAIL = 2;
    const STATUS_DEACTIVATE = 0;

    protected $_origEmailDesignConfig;

    public function isModuleActive($store = null)
    {
        $value = Mage::getStoreConfig(self::XML_PATH_MODULE_DISABLED, $store);
        return ! $value;
    }
    
    public function isModuleActiveInAdmin()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ALWAYS_ACTIVE_ADMIN)) {
            return true;
        }
        return $this->isModuleActive(0);
    }
    
    /**
     * Send Admin a notification whenever a new customer account is registered
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Netzarbeiter_CustomerActivation_Helper_Data
     */
    public function sendAdminNotificationEmail(Mage_Customer_Model_Customer $customer)
    {
        $storeId = $this->getCustomerStoreId($customer);
        if (Mage::getStoreConfig(self::XML_PATH_ALERT_ADMIN, $storeId)) {
            $to = $this->_getEmails(self::XML_PATH_EMAIL_ADMIN_NOTIFICATION, $storeId);
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
            $this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE, $storeId);
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
        if (Mage::getStoreConfig(self::XML_PATH_ALERT_CUSTOMER, $this->getCustomerStoreId($customer))) {
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
     * @param int $storeId
     * @return Netzarbeiter_CustomerActivation_Helper_Data
     */
    protected function _sendNotificationEmail($to, $customer, $templateConfigPath, $storeId = null)
    {
        if (!$to) return;

        if (is_null($storeId)) {
            $storeId = $this->getCustomerStoreId($customer);
        }

        $translate = Mage::getSingleton('core/translate')
            ->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');

        $template = Mage::getStoreConfig($templateConfigPath, $storeId);

        $sendTo = array();
        foreach ($to as $recipient) {
            if (is_array($recipient)) {
                $sendTo[] = $recipient;
            } else {
                $sendTo[] = array('email' => $recipient, 'name' => null);
            }
        }

        $this->_setEmailDesignConfig($mailTemplate, $storeId);

        foreach ($sendTo as $recipient) {
            $mailTemplate->sendTransactional(
                    $template,
                    Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $storeId),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'customer' => $customer,
                        'shipping' => $customer->getPrimaryShippingAddress(),
                        'billing' => $customer->getPrimaryBillingAddress(),
                        'store' => Mage::app()->getStore(
                            // In case of admin store emails, $storeId is set to 0.
                            // We want 'store' to always be set to the customers store.
                            $this->getCustomerStoreId($customer)
                        ),
                    ),
                    $storeId
                );
        }

        $this->_revertEmailDesignConfig($mailTemplate);

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Keep the original design config if it is set so it can be reset later
     *
     * @param Mage_Core_Model_Email_Template $mailTemplate
     * @param int $storeId
     * @return $this
     */
    protected function _setEmailDesignConfig(Mage_Core_Model_Email_Template $mailTemplate, $storeId)
    {
        $this->_origEmailDesignConfig = null;

        // Workaround for bug in Mage_Core_Model_Template where getDesignConfig is protected
        if (is_callable(array($mailTemplate, 'getDesignConfig'))) {
            // Use standard way to fetch the current design config (if possible)
            $this->_origEmailDesignConfig = $mailTemplate->getDesignConfig();
            
        } elseif (version_compare(phpversion(), '5.3.2', '>=')) {
            // ReflectionMethod::setAccessible() is only available in 5.3.2 or newer
            $method = new ReflectionMethod($mailTemplate, 'getDesignConfig');
            if ($method->isProtected()) {
                $method->setAccessible(true);
            }
            if ($this->_origEmailDesignConfig = $method->invoke($mailTemplate)) {
                $this->_origEmailDesignConfig = $this->_origEmailDesignConfig->getData();
            }
        }

        // Fallback if neither of the previous versions is available or if 
        // there was no design configuration set on the mail template instance
        if (! $this->_origEmailDesignConfig) {
            $this->_origEmailDesignConfig = array(
                'area' => Mage::app()->getStore()->isAdmin() ? 'adminhtml' : 'frontend',
                'store' => Mage::app()->getStore()->getId()
            );
        }

        $mailTemplate->setDesignConfig(array(
                'area' => Mage::app()->getStore($storeId)->isAdmin() ? 'adminhtml' : 'frontend',
                'store' => $storeId)
        );

        return $this;
    }

    /**
     * Reset the design configuration so emails sent later during
     * the request will use the right store config
     *
     * @param Mage_Core_Model_Email_Template $mailTemplate
     * @return $this
     */
    protected function _revertEmailDesignConfig(Mage_Core_Model_Email_Template $mailTemplate)
    {
        $mailTemplate->setDesignConfig($this->_origEmailDesignConfig);
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
        if (!($storeId = $customer->getSendemailStoreId())) {
            /*
             * store_id might be zero if the account was created in the admin interface
             */
            $storeId = $customer->getStoreId();
            if (!$storeId && $customer->getWebsiteId()) {
                /*
                 * Use the default store groups store of the customers website
                 */
                if ($store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()) {
                    $storeId = $store->getId();
                }
            }
            // In case the website_id is not yet set on the customer, and the
            // current store is a frontend store, use the current store ID
            if (!$storeId && !Mage::app()->getStore()->isAdmin()) {
                $storeId = Mage::app()->getStore()->getId();
            }
        }
        return $storeId;
    }

    /**
     * Return the default activation status for a given group and store Id
     *
     * @param int $groupId
     * @param int $storeId
     * @return bool
     */
    public function getDefaultActivationStatus($groupId, $storeId)
    {
        $defaultIsActive = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS, $storeId);
        $activateByGroup = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS_BY_GROUP, $storeId);
        
        if (! $defaultIsActive && $activateByGroup) {
            $notActiveGroups = explode(',', Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS_GROUPS, $storeId));
            $isActive = in_array($groupId, $notActiveGroups) ? false : true;
        } else {
            $isActive = $defaultIsActive;
        }

        return $isActive;
    }
}
