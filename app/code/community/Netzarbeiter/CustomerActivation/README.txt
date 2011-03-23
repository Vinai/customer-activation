
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


Magento Module: Netzarbeiter/CustomerActivation
Author: Vinai Kopp <vinai@netzarbeiter.com>


USAGE

This small extension makes it impossible for a customer to log in to magento,
until the account has been activated in the adminhtml interface.

Customers > Manage Customers > (select customer) > Account Information > Is activated

The Extension was designed to be used together with the Netzarbeiter_LoginCatalog extension
Netzarbeiter_LoginCatalog only shows products to customers that are logged in.
There also is the Extension Netzarbeiter_GroupsCatalog which may suit your needs better,
you have to evaluate yourself what is the best solution for your case.

You can disable the Extension on a global or website level using the options found under
System > Configuration > Customer > Customer Activation

You can also select email notifications for the admin (when a new customer registers) and
for the customer (when her account has been activated).

With the latest release this module now also works when customer email confirmation is turned
off.

Thanks to Junya Sano for the japanese translation!
Thanks to SeL for the french translation and the bugfix!
Thanks to SeL also for reporting the backend customer creation bug.
Thanks to Toon van Veelen for the dutch translation!
Thanks to Erik Hoeksma for the Greek translation!
Thanks to PaulE for the integration in the admin grid for mass activation of customers!
Thanks to Finn Snaterse for the inclusion in the Adminhtml Grid!
Thanks to Aaron Kondziela for reminding me to clean up the admin customer edit page stuff!
Thanks to Chiara Piatti for the italian translation!


KNOWN BUGS:
- None! :D

If you have ideas for improvements or find bugs, please send them to vinai@netzarbeiter.com,
with Netzarbeiter_CustomerActivation as part of the subject line.

