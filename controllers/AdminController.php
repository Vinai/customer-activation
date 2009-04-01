<?php

class Netzarbeiter_CustomerActivation_AdminController extends Mage_Adminhtml_Controller_Action
{
	public function massActivationAction()
	{
		$customerIds = $this -> getRequest() -> getParam('customer');

		if(!is_array($customerIds))
		{
			Mage::getSingleton('adminhtml/session') -> addError(Mage::helper('adminhtml') -> __('Please select item(s)'));
		}
		else
		{
			$model = Mage::getModel('customer/customer');
			try
			{
				foreach($customerIds as $customerId)
				{
					$model	-> load($customerId);
					$model -> setCustomerActivated($this -> getRequest() -> getParam('customer_activated')) -> save();
				}

				Mage::getSingleton('adminhtml/session') -> addSuccess(
					Mage::helper('adminhtml') -> __(
						'Total of %d record(s) were successfully saved', count($customerIds)
					)
				);
			}
			catch(Exception $e)
			{
				Mage::getSingleton('adminhtml/session') -> addError($e -> getMessage());
			}
		}

		$this -> _redirect('adminhtml/customer');
	}
}