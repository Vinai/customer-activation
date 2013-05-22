<?php


class Netzarbeiter_CustomerActivation_Test_Helper_Data_Mock_Zend_Mail
    extends Zend_Mail
{
    protected $_sendCount = 0;

    /**
     * Disable the send() method, retain all other methods.
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @return Zend_Mail
     */
    public function send($transport = null)
    {
        $transport; // Not needed, but just in case
        $this->_sendCount++;
        return $this;
    }

    public function getSendCount()
    {
        return $this->_sendCount;
    }

    public function setSendCount($count)
    {
        $this->_sendCount = (int) $count;
    }
}