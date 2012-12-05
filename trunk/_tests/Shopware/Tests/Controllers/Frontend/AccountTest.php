<?php
/**
 * Test case for Account Controller
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Account
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Frontend_AccountTest extends Enlight_Components_Test_Controller_TestCase
{

    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Partner').'Partner.xml');
    }

    /**
     * test testPartnerStatistic controller action
     *
     * @return array|int|string $id
     */
    public function testPartnerStatistic()
    {
        //Login to the frontend
        $this->Request()
                ->setMethod('POST')
                ->setPost('email', 'hl@shopware.de')
                ->setPost('password', 'shopware');
        $this->dispatch('/account/login');
        $this->assertTrue($this->Response()->isRedirect());
        $this->reset();

        //setting date range
        $params["fromDate"] = "01.01.2000";
        $params["toDate"] = "01.01.2222";
        $this->Request()->setParams($params);
        Shopware()->Session()->partnerId = 1;

        $this->dispatch('/account/partnerStatistic');
        $this->assertEquals("01.01.2000", $this->View()->partnerStatisticFromDate);
        $this->assertEquals("01.01.2222", $this->View()->partnerStatisticToDate);
        $chartData = $this->View()->sPartnerOrderChartData[0];

        $this->assertTrue(($chartData["date"] instanceof \DateTime));
        $this->assertTrue(!empty($chartData["timeScale"]));
        $this->assertTrue(!empty($chartData["netTurnOver"]));
        $this->assertTrue(!empty($chartData["provision"]));
    }
}