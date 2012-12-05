<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     M.Schmaeing
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Notification
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_NotificationTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Returns the test DataSet
     * Because of this DataSet you can assert fix values
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Articles').'Notification.xml');
    }

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();
        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * test getList controller action
     */
    public function testGetArticleList()
    {
        $this->dispatch('backend/Notification/getArticleList');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertNotEmpty($returnData);
        $this->assertEquals(2,count($returnData));
        $listingFirstEntry = $returnData[0];

        // cause of the DataSet you can assert fix values
        $this->assertEquals(2, $listingFirstEntry["registered"]);
        $this->assertEquals("SW2001", $listingFirstEntry["number"]);
        $this->assertEquals(1, $listingFirstEntry["notNotified"]);
    }

    /**
     * test getCustomerList controller action
     */
    public function testGetCustomerList()
    {
        $params["orderNumber"] = "SW2001";
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertEquals(2,count($returnData));
        $listingFirstEntry = $returnData[0];
        $listingSecondEntry = $returnData[1];

        // cause of the DataSet you can assert fix values
        $this->assertEquals("ms@shopware.de", $listingFirstEntry["mail"]);
        $this->assertEquals(0, $listingFirstEntry["notified"]);

        $this->assertEquals("ms@shopware.ag", $listingSecondEntry["mail"]);
        $this->assertEquals(1, $listingSecondEntry["notified"]);


        $params["orderNumber"] = "SW2003";
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertEquals(1,count($returnData));
        $this->assertEquals("hl@shopware.de", $returnData[0]["mail"]);
        $this->assertTrue(!empty($returnData[0]["name"]));
        $this->assertTrue(!empty($returnData[0]["customerId"]));
    }
}
