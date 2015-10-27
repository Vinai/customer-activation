Customer Activation
============================
Make it impossible for a customer to log in until the account has been activated by the admin.

Facts
-----
- version: check the [config.xml](https://github.com/Vinai/customer-activation/blob/master/app/code/community/Netzarbeiter/CustomerActivation/etc/config.xml)
- extension key: Netzarbeiter_CustomerActivation
- extension on Magento Connect: -
- Magento Connect 1.0 extension key: -
- Magento Connect 2.0 extension key: -
- [extension on GitHub](https://github.com/Vinai/customer-activation)
- [direct download link](https://github.com/Vinai/customer-activation/zipball/master)

Description
-----------
This small extension makes it impossible for a customer to log in to magento,
until the account has been activated in the adminhtml interface.

Customers - Manage Customers - (select customer) - Account Information - Is activated

You can also select email notifications for the admin (when a new customer registers) and
for the customer (when her account has been activated).

New customers can be configured to be activated by default. This can also be done on a per group basis.

The Extension was designed to be used together with the [Netzarbeiter_LoginCatalog][] extension.
Netzarbeiter_LoginCatalog only shows products to customers that are logged in.
There also is the Extension [Netzarbeiter_GroupsCatalog2][] which may suit your needs better,
you have to evaluate yourself what is the best solution for your case.

[Netzarbeiter_LoginCatalog]: https://github.com/Vinai/loginonlycatalog/ "The Login only Catalog Extension on github"
[Netzarbeiter_GroupsCatalog2]: https://github.com/Vinai/groupscatalog2 "GroupsCatalog 2"

Compatibility
-------------
- Magento >= 1.4

Installation Instructions
-------------------------
1. Install the extension via Magento Connect with the key shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. Configure and activate the extension under System - Configuration - Customer Configuration - Customer Activation

If you have existing customers prior to installing the extension, they will be deactivated by default.  
You can easily and quickly activate all existing accounts using the mass action found above the customer backend grid.

Uninstallation Instructions
---------------------------
1. Delete the file app/etc/modules/Netzarbeiter_CustomerActivation.xml
2. Execute the following SQL:
```
   DELETE FROM eav_attribute WHERE attribute_code = 'customer_activated';
```
```
   DELETE FROM core_resource WHERE code = 'customeractivation_setup';
```
3. Remove all remaining extension files
   - app/code/community/Netzarbeiter/CustomerActivation/
   - app/locale/*/Netzarbeiter_CustomerActivation.csv
   - app/locale/*/template/email/netzarbeiter/customeractivation

Acknowledgements
----------------
- Thanks to Max for the updated french translation!
- Thanks to Junya Sano for the japanese translation!
- Thanks to SeL for the french translation and the bugfix!
- Thanks to SeL also for reporting the backend customer creation bug.
- Thanks to Toon van Veelen for the dutch translation!
- Thanks to Erik Hoeksma for the Greek translation!
- Thanks to PaulE for the integration in the admin grid for mass activation of customers!
- Thanks to Finn Snaterse for the inclusion in the Adminhtml Grid!
- Thanks to Aaron Kondziela for reminding me to clean up the admin customer edit page stuff!
- Thanks to Chiara Piatti for the italian translation!

Support
-------
If you have any issues with this extension, open an issue on GitHub (see URL above)

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Vinai Kopp  
[http://www.netzarbeiter.com](http://www.netzarbeiter.com)  
[@VinaiKopp](https://twitter.com/VinaiKopp)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2015 Vinai Kopp
