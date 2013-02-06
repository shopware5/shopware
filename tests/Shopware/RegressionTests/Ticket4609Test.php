<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4609
 */
class Shopware_RegressionTests_Ticket4609 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Newsletter').'Log.xml');
    }

    /**
     * Test case method
     */
    public function testNewsletterLog()
    {
        $this->Front()->setParam('noViewRenderer', false);

        $e = null;

        try {
            $this->dispatch('/backend/newsletter/log/mailling/1/mailaddress/70');
        } catch (Exception $e) { }

        $this->assertNull($e);
    }
}
