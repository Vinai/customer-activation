<?php


class Netzarbeiter_CustomerActivation_Test_Controller_Adminhtml_AbstractController
    extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * Mock the admin session and the adminhtml notifications
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockAdminSession();
        $this->disableAdminNotifications();
    }

    /**
     * Build the admin session mock
     */
    protected function mockAdminSession()
    {
        $mockUser = $this->getModelMock('admin/user');
        $mockUser->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockSession = $this->getModelMockBuilder('admin/session')
            ->disableOriginalConstructor()
            ->setMethods(array('isLoggedIn', 'getUser', 'refreshAcl', 'isAllowed'))
            ->getMock();

        $mockSession->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $mockSession->expects($this->any())
            ->method('refreshAcl')
            ->will($this->returnSelf());

        $mockSession->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->replaceByMock('model', 'admin/user', $mockUser);

        $mockSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($mockUser));

        $this->replaceByMock('singleton', 'admin/session', $mockSession);
    }

    /**
     * Disable the admin notification rss feed
     */
    protected function disableAdminNotifications()
    {
        // Disable notification feed during test
        $mockFeed = $this->getModelMockBuilder('adminnotification/feed')
            ->disableOriginalConstructor()
            ->setMethods(array('checkUpdate', 'getFeedData'))
            ->getMock();
        $mockFeed->expects($this->any())
            ->method('checkUpdate')
            ->will($this->returnSelf());
        $mockFeed->expects($this->any())
            ->method('getFeedData')
            ->will($this->returnValue(''));

        $this->replaceByMock('model', 'adminnotification/feed', $mockFeed);
    }
}