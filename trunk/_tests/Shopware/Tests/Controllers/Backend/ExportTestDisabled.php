<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Tests
 */
class Shopware_Tests_Controllers_Backend_ExportTest extends Enlight_Components_Test_Controller_TestCase
{
	/**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
    	return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Export').'Bom.xml');
    }
	
	/**
     * Test case method
     * 
     * @ticket 4887
     */
	public function testIndex()
	{
		ob_start();
		$this->dispatch('/backend/export/index/export.txt?feedID=1&hash=0805bbb934287228edb5374083b81416');
		$content = ob_get_clean();

		$this->assertNotEmpty($content);
		$this->assertEquals("\xEF\xBB\xBF", substr($content, 0, 3));
		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}

    /**
     * expected that an inactive export returns nothing
     *
     */
    public function testInActiveExport()
    {
        $this->assertLinkNotExists("/backend/export/index/preissuchmaschine.csv?feedID=16&hash=77c6f9b3a431dde220c8bbd3b5b0ba79");
    }
}