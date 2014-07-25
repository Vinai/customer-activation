<?php

class Netzarbeiter_CustomerActivation_Test_Controller_Adminhtml_CustomerGridTest
    extends Netzarbeiter_CustomerActivation_Test_Controller_Adminhtml_AbstractController
{
    protected function _overloadExit()
    {
        if (function_exists('set_exit_overload')) {
            set_exit_overload(function () {
                return false;
            });
            return true;
        } elseif (function_exists('uopz_overload')) {
            if (! ini_get('uopz.overloads')) {
                $this->markTestSkipped('uopz extension installed but uopz.overloads ini setting is disabled.');
            }
            uopz_overload(ZEND_EXIT, function(){});
            return true;
        }
        return false;
    }
    
    protected function _restoreExit()
    {
        if (function_exists('unset_exit_overload')) {
            unset_exit_overload();
        } elseif (function_exists('uopz_overload')) {
            uopz_overload(ZEND_EXIT, null);
        }
    }
    
    /**
     * Requires krakjoe/uopz or phpunit/test_helpers to be installed so exit() can be overloaded.
     *
     * See https://github.com/krakjoe/uopz
     * and https://github.com/sebastianbergmann/php-test-helpers
     * and https://github.com/whatthejeff/php-test-helpers (a pull request so it compiles for PHP 5.4)
     *
     * @param string $route
     * @throws Exception|Zend_Controller_Response_Exception
     * @return string
     */
    protected function getResponseFromActionWithExit($route)
    {
        if (! $this->_overloadExit()) {
            $this->markTestSkipped("Unable to overload exit(): uopz or phpunit/test_helpers zend extensions not installed.");
        }

        try {
            ob_start();
            $this->dispatch($route);
            $this->_restoreExit();
        } catch (Zend_Controller_Response_Exception $e) {
            $this->_restoreExit();
            if ($e->getMessage() !== 'Cannot send headers; headers already sent') {
                ob_end_clean();
                throw $e;
            }
        }
        $responseBody = ob_get_contents();
        ob_end_clean();
        return $responseBody;
    }

    /**
     *
     * @param string $action
     *
     * @test
     * @singleton admin/session
     * @dataProvider activationStatusGridModificationsProvider
     */
    public function activationStatusGridModifications($action)
    {
        $this->dispatch('adminhtml/customer/' . $action);

        $this->assertLayoutHandleLoaded('adminhtml_customer_' . $action);
        $this->assertEventDispatched('eav_collection_abstract_load_before');

        // Check grid block is instantiated
        $gridBlock = $this->_getCustomerGridBlock();
        $this->assertInternalType('object', $gridBlock, "Customer grid block not found");
        $this->assertInstanceOf('Mage_Adminhtml_Block_Customer_Grid', $gridBlock);

        // Check if customer_activation column is defined
        $foundActivationCol = $gridBlock->getColumn('customer_activated') !== false;
        $this->assertTrue($foundActivationCol, "Customer activation column not found in grid");

        // Check mass action is defined
        /** @var Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract $massActionBlock */
        $massActionBlock = $gridBlock->getMassactionBlock();
        $massAction = $massActionBlock->getItem('customer_activated');
        $this->assertTrue((bool) $massAction, "Customer activation mass action not found");

        // Check customer activation attribute was loaded on customer collection
        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = $gridBlock->getCollection();
        $property = new ReflectionProperty($collection, '_selectAttributes');
        $property->setAccessible(true);
        $selectAttributes = $property->getValue($collection);

        $this->assertArrayHasKey(
            'customer_activated', $selectAttributes, "Customer activation attribute not part of collection"
        );
    }

    /**
     * @return Mage_Adminhtml_Block_Customer_Grid
     */
    protected function _getCustomerGridBlock()
    {
        foreach ($this->app()->getLayout()->getAllBlocks() as $block) {
            if ($block->getType() === 'adminhtml/customer_grid') {
                return $block;
            }
        }
        return null;
    }

    /**
     * Data Provider for activationStatusGridModifications test
     * with adminhtml customer controller actions.
     *
     * @return array
     */
    public function activationStatusGridModificationsProvider()
    {
        return array(
            array('index'),
            array('grid'),
        );
    }

    /**
     * @test
     */
    public function activationStatusInCsvExport()
    {
        $body = $this->getResponseFromActionWithExit('adminhtml/customer/exportCsv');

        $this->assertResponseHeaderEquals('content-type', 'application/octet-stream');

        $label = 'Customer Activated';

        list($exportHeaders) = explode("\n", $body);
        $columns = str_getcsv($exportHeaders);

        $this->assertTrue(in_array($label, $columns), "Column \"$label\" not found in CSV export columns");
    }

    /**
     * @test
     */
    public function activationStatusInExcelExport()
    {
        $body = $this->getResponseFromActionWithExit('adminhtml/customer/exportXml');

        $this->assertResponseHeaderEquals('content-type', 'application/octet-stream');

        $label = 'Customer Activated';

        /** @var SimpleXmlElement $xml */
        $xml = simplexml_load_string($body);
        $found = false;
        foreach ($xml->Worksheet->children() as $worksheet) {
            foreach ($worksheet->children() as $columns) {
                foreach ($columns->children() as $cell) {
                    $value = (string) $cell->Data;
                    if ($value == $label) {
                        $found = true;
                        break(3);
                    }
                }
                // Only check the first row
                break(2);
            }
        }

        $this->assertTrue($found, "Column \"$label\" not found in Excel export columns");
    }
}