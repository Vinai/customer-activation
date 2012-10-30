Customer Activation
============================
Make it impossible for a customer to log in until the account has been activated by the admin.

Facts
-----
- version: check the [config.xml](https://github.com/Vinai/customer-activation/blob/master/app/code/community/Netzarbeiter/CustomerActivation/etc/config.xml)
- extension key: Netzarbeiter_CustomerActivation
- [extension on Magento Connect](http://www.magentocommerce.com/magento-connect/customer-activation.html)
- Magento Connect 1.0 extension key: magento-community/Netzarbeiter_CustomerActivation
- Magento Connect 2.0 extension key: http://connect20.magentocommerce.com/community/Netzarbeiter_CustomerActivation
- [extension on GitHub](https://github.com/Vinai/customer-activation)
- [direct download link](https://github.com/Vinai/customer-activation/zipball/master)

Description
-----------
The Extension was designed to be used together with the [Netzarbeiter_LoginCatalog][] extension.
Netzarbeiter_LoginCatalog only shows products to customers that are logged in.
There also is the Extension [Netzarbeiter_GroupsCatalog2][] which may suit your needs better,
you have to evaluate yourself what is the best solution for your case.

This module can also be installed from [Magento Connect][mc].

[mc]: http://www.magentocommerce.com/magento-connect/customer-activation.html "The Customer Activation Extension on Magento Connect"
[Netzarbeiter_LoginCatalog]: http://www.magentocommerce.com/magento-connect/login-only-catalog.html "The Login only Catalog Extension on Magento Connect"
[Netzarbeiter_GroupsCatalog2]: https://github.com/Vinai/groupscatalog2 "GroupsCatalog 2"

Compatibility
-------------
- Magento >= 1.4

Installation Instructions
-------------------------
1. Install the extension via Magento Connect with the key shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. Configure and activate the extension under System - Configuration - Customer Configuration - Customer Activation

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
(c) 2012 Vinai Kopp