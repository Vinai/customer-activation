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
 * @category   Mage
 * @package    Netzarbeiter_CustomerActivation
 * @copyright  Copyright (c) 2008 Vinai Kopp (http://netzarbeiter.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account form block extension for Netzarbeiter_CustomerActivation
 *
 * @category   Mage
 * @package    Netzarbeiter_CustomerActivation
 * @author     Vinai Kopp
 */
class Netzarbeiter_CustomerActivation_Block_Edit_Tab_Account extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{

	/**
	 * Make a select element instead of a text field for the customer_activation
	 * attribute in the adminhtml interface
	 *
	 * Customer > Manage Customers > Choose Customer > Account Information > Is activated
	 */
    public function initForm()
    {
		parent::initForm();
		$customer = Mage::registry('current_customer');
        
        // do not check if customer is activated, so the create new customer
        // form in the adminhtml works (Once again thanks to SeL for reporting the bug!)
        
		$activationAttribute = $customer->getAttribute('customer_activated');

		$fieldset = $this->getForm()->getElement('base_fieldset'); // fieldset form element

		// remove default text field element
		$fieldset->removeField('customer_activated');
			
		/**
		 * Thanks to SeL for this bugfix!
		 */
		if (Mage::getStoreConfig('customer/customeractivation/disable_ext', $customer->getStoreId())) return $this;

		// add new select element
		$element = $fieldset->addField('customer_activated', 'select', array(
			'name' => 'customer_activated',
			'label' => Mage::helper('customer')->__($activationAttribute->getFrontendLabel()),
		));
		$element->setEntityAttribute($activationAttribute)->setValues(array(
			0 => Mage::helper('customer')->__('Deactivated'),
			1 => Mage::helper('customer')->__('Activated')
		));
		$element->setValue((int) $customer->getData('customer_activated'));
		
		return $this;
	}
}
