<?php

/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_CustomerActivation
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @see Netzarbeiter_CustomerActivation_Helper_Data
 *
 * @loadSharedFixture global.yaml
 * @doNotIndexAll
 */
class Netzarbeiter_CustomerActivation_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Mock of Zend_Mail instance to access properties after send() is called
     *
     * @var Netzarbeiter_CustomerActivation_Test_Helper_Data_Mock_Zend_Mail
     */
    protected $_mail = null;

    /**
     * @var string
     */
    protected $_originalLocale = null;

    /**
     * Ssave the original locale
     */
    protected function setUp()
    {
        if (is_null($this->_originalLocale)) {
            $this->_originalLocale = $this->app()->getLocale()->getLocaleCode();
        }
    }

    /**
     * Restore the admin store score, reset mail send count and original locale
     */
    protected function tearDown()
    {
        if ($this->_mail) {
            $this->_mail->setSendCount(0);
            $this->_mail->clearRecipients()->setParts(array());
        }
        if (isset($this->_originalLocale)) {
            $this->app()->getLocale()->setLocaleCode($this->_originalLocale);
        }
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getMockCustomer()
    {
        $mockCustomer = $this->getModelMockBuilder('customer/customer')
            ->disableOriginalConstructor()
            ->setMethods(array('getName', 'getEmail', 'getSendemailStoreId', 'getStoreId', 'getAddressesCollection'))
            ->getMock();
        $mockCustomer->expects($this->any())
            ->method('getSendemailStoreId')
            ->will($this->returnValue(null));
        $mockCustomer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($this->app()->getStore('usa')->getId()));
        $mockCustomer->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('John Doe'));
        $mockCustomer->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('John.Doe@example.com'));
        $mockCustomer->expects(($this->any()))
            ->method('getAddressesCollection')
            ->will($this->returnValue(new Varien_Object()));

        return $mockCustomer;
    }

    /**
     * Inject a mocked Zend_Mail instance into core/email_instance
     *
     * The purpose is to avoid calls to send() to actually do something. Keep
     * all other functionality intact.
     */
    protected function _prepareEmailTemplateInstance()
    {
        $this->_mail = new Netzarbeiter_CustomerActivation_Test_Helper_Data_Mock_Zend_Mail();

        $mailTemplate = Mage::getModel('core/email_template');
        $property = new ReflectionProperty($mailTemplate, '_mail');
        $property->setAccessible(true);
        $property->setValue($mailTemplate, $this->_mail);

        // Unable to use $this->replaceByMock() because $mailTemplate is no instance
        // of PHPUnit_Framework_MockObject_MockObject
        $this->app()->getConfig()->replaceInstanceCreation('model', 'core/email_template', $mailTemplate);
    }

    /**
     * Simple data provider of locales and matching strings from the email template
     *
     * @return array
     */
    public function sendCustomerNotificationEmailDataProvider()
    {
        return array(
            //    Locale   A text Snipplet from the locales default template
            array('en_US', 'your account has been activated.'), // Standard locale
            array('xx_XX', 'your account has been activated.'), // A non-existent locale falls back to default
            array('de_DE', 'ihr Konto wurde aktiviert.'),       // German locale with different template
        );
    }

    /**
     * @param string $locale
     * @param string $contentPart
     *
     * @test
     * @loadFixture emailConfig.yaml
     * @dataProvider sendCustomerNotificationEmailDataProvider
     */
    public function sendCustomerNotificationEmail($locale, $contentPart)
    {
        $mockCustomer = $this->_getMockCustomer();

        $this->app()->getStore($mockCustomer->getStoreId())->setConfig('general/locale/code', $locale);


        $this->_prepareEmailTemplateInstance();

        Mage::helper('customeractivation')->sendCustomerNotificationEmail($mockCustomer);

        $message = sprintf(
            "Expected method send() to be called 1 time but found to be called %s time(s)",
            $this->_mail->getSendCount()
        );
        $this->assertEquals(1, $this->_mail->getSendCount(), $message);

        $this->assertContains(
            $mockCustomer->getEmail(), $this->_mail->getRecipients(),
            "Not found the customer email in recipient list of email instance"
        );

        $this->assertContains($contentPart, $this->_mail->getBodyHtml(true));
    }

    /**
     * The admin email should not change with the customer store locale, so the text sample is the same always
     * 
     * @return array
     */
    public function sendAdminNotificationEmailDataProvider()
    {
        return array(
         // customer Locale  admin locale,  A text Snipplet from the locales default template
            array('en_US', 'en_US', 'New customer registration at'), // Standard locale
            array('xx_XX', 'en_US', 'New customer registration at'), // A non-existent locale
            array('de_DE', 'en_US', 'New customer registration at'), // German locale still uses en_US for admin email
            array('de_DE', 'de_DE', 'Neue Kundenregistrierung bei'), // German locale in admin
            array('en_US', 'de_DE', 'Neue Kundenregistrierung bei'), // German locale in admin
        );
    }

    /**
     * @param string $customerLocale
     * @param string $contentPart
     *
     * @test
     * @loadFixture emailConfig.yaml
     * @dataProvider sendAdminNotificationEmailDataProvider
     */
    public function sendAdminNotificationEmail($customerLocale, $adminLocale, $contentPart)
    {
        $mockCustomer = $this->_getMockCustomer();

        // Config fixtures
        $this->app()->getStore($mockCustomer->getStoreId())
            ->setConfig('general/locale/code', $customerLocale)
            ->setConfig('customer/customeractivation/alert_admin', 1);
        
        $this->app()->getStore('admin')
            ->setConfig('general/locale/code', $adminLocale);
        
        $this->_prepareEmailTemplateInstance();

        Mage::helper('customeractivation')->sendAdminNotificationEmail($mockCustomer);

        $message = sprintf(
            "Expected method send() to be called 1 time but found to be called %s time(s)",
            $this->_mail->getSendCount()
        );
        $this->assertEquals(1, $this->_mail->getSendCount(), $message);

        $this->assertNotContains(
            $mockCustomer->getEmail(), $this->_mail->getRecipients(),
            "Found the customer email in recipient list for admin email"
        );

        $this->assertContains($contentPart, $this->_mail->getBodyHtml(true));
    }
}