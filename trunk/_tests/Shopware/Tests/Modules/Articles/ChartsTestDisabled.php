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
class Shopware_Tests_Modules_Articles_ChartsTest extends Enlight_Components_Test_TestCase
{
    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;
    
    /**
     * Test category id
     *
     * @var array
     */
    protected $testCategoryId = 3;
    
    /**
     * Test set up method
     */
    protected function setUp()
    {
        parent::setUp();
                
        $this->module = Shopware()->Modules()->Articles();
    }
            
    /**
     * Retrieve module instance
     * 
     * @return sArticles
     */
    public function Module()
    {
        return $this->module;
    }
    
    /**
     * Test case method
     * 
     * @ticket 4355 
     */
    public function testCharts()
    {
    	$charts = $this->Module()->sGetArticleCharts($this->testCategoryId);

    	$this->assertArrayCount((int) Shopware()->Config()->ChartRange, $charts);
    	$this->assertEquals(13, (int) $charts[0]['articleID']);
    	$this->assertEquals(131, (int) $charts[1]['articleID']);
    }
}