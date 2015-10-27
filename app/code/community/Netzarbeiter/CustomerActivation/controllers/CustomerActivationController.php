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

class Netzarbeiter_CustomerActivation_CustomerActivationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Activate or deactivate all selected customers
     */
    public function massActivationAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');

        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('customeractivation')->__('Please select item(s)')
            );
        } else {
            $paramValue = $this->getRequest()->getParam('customer_activated');

            try {
                $updatedCustomerIds = Mage::getResourceModel('customeractivation/customer')
                    ->massSetActivationStatus(
                        $customerIds, $this->_shouldSetToActivated($paramValue)
                    );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('customeractivation')->__(
                        'Total of %d record(s) were successfully saved', count($updatedCustomerIds)
                    )
                );

                if ($this->_shouldSendActivationNotification($paramValue)) {
                    $this->_sendActivationNotificationEmails($updatedCustomerIds);
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/customer');
    }

    /**
     * Based on the mass action query parameter, should customers be activated or not.
     *
     * @param $paramValue
     * @return int
     */
    protected function _shouldSetToActivated($paramValue)
    {
        switch ($paramValue) {
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITH_EMAIL:
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITHOUT_EMAIL:
                $activationStatus = 1;
                break;
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_DEACTIVATE:
            default:
                $activationStatus = 0;
                break;
        }
        return $activationStatus;
    }

    /**
     * Based on the mass action query parameter, should customer notifications be sent or not
     *
     * @param $paramValue
     * @return bool
     */
    protected function _shouldSendActivationNotification($paramValue)
    {
        switch ($paramValue) {
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITH_EMAIL:
                $sendEmail = true;
                break;
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITHOUT_EMAIL:
            case Netzarbeiter_CustomerActivation_Helper_Data::STATUS_DEACTIVATE:
            default:
                $sendEmail = false;
                break;
        }
        return $sendEmail;
    }

    /**
     * Send notification emails to all selected customers
     *
     * @param array $customerIds
     */
    protected function _sendActivationNotificationEmails(array $customerIds)
    {
        $helper = Mage::helper('customeractivation');
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $customerIds))
            ->addAttributeToSelect('*')
            ->addNameToSelect();
        foreach ($customers as $customer) {
            $helper->sendCustomerNotificationEmail($customer);
        }
    }

    /**
     * Check the admin user is allowed to manage customer accounts
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
