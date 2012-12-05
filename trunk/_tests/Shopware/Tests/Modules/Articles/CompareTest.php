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
class Shopware_Tests_Modules_Articles_CompareTest extends Enlight_Components_Test_Database_TestCase
{
    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;
    
    /**
     * Test article ids
     *
     * @var array
     */
    protected $testArticleIds;
    
    /**
     * Test set up method
     */
    protected function setUp()
    {
        parent::setUp();
                
        $this->module = Shopware()->Modules()->Articles();
        
        $sql = 'SELECT `id` FROM `s_articles` WHERE `active` =1';
        $sql = Shopware()->Db()->limit($sql, 5);
        $this->testArticleIds = Shopware()->Db()->fetchCol($sql);
    }
    
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Articles').'Compare.xml');
    }
    
    /**
     * Returns a test article id
     * 
     * @return int
     */
    protected function getTestArticleId()
    {
        return array_shift($this->testArticleIds);
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
     */
    public function testDeleteComparison()
    {
        //TODO - Activate after Model-Update
        return;

        $article = $this->getTestArticleId();
        $this->assertTrue($this->Module()->sAddComparison($article));
        $this->Module()->sDeleteComparison($article);
        $this->assertEmpty($this->Module()->sGetComparisons());
    }
    
    /**
     * Test case method
     */
    public function testDeleteComparisons()
    {
        //TODO - Activate after Model-Update
        return;

        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        
        $this->Module()->sDeleteComparisons();
        $this->assertEmpty($this->Module()->sGetComparisons());
    }
    
    /**
     * Test case method
     */
    public function testAddComparison()
    {
        //TODO - Activate after Model-Update
        return;

        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertNotEmpty($this->Module()->sGetComparisons());
    }
    
    /**
     * Test case method
     */
    public function testGetComparisons()
    {
        //TODO - Activate after Model-Update
        return;

        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertEquals(count($this->Module()->sGetComparisons()), 2);
    }
    
    /**
     * Test case method
     */
    public function testGetComparisonList()
    {
        //TODO - Activate after Model-Update
        return;

        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        $this->assertEquals(count($this->Module()->sGetComparisonList()), 2);
    }
}