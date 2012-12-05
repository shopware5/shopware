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
class Shopware_Tests_Modules_Articles_TranslationTest extends Enlight_Components_Test_Database_TestCase
{
    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;
	
    /**
     * Test set up method
     */
    protected function setUp()
    {
        parent::setUp();
                
        $this->module = Shopware()->Modules()->Articles();
        
        $sql = "
        	INSERT IGNORE INTO `s_core_multilanguage` (
        		`id`, `isocode`, `locale`, `parentID`, `flagstorefront`, `flagbackend`, `skipbackend`,
        		`name`, `defaultcustomergroup`, `template`, `doc_template`, `separate_numbers`, `domainaliase`,
        		`defaultcurrency`, `default`, `switchCurrencies`, `switchLanguages`, `fallback`,
        		`encoding`, `navigation`, `inheritstyles`
        	) VALUES (
				6, 'en', '2', 4, '', 'gb.png', 0,
				'Englisch', 'EK', 'templates/orange', 'templates/orange', 1, '',
				2, 0, '1|2', '1|6', '',
				'', 'gLeft:eLeft;gBottom:eBottom;gBottom2:eBottom2;', 0
			);
        ";
        Shopware()->Db()->exec($sql);
        
        SHopware()->Config()->CacheTranslationTable = 0;
    }
    
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Articles').'Translation.xml');
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
     * @ticket 4568
     */
    public function testCreateTranslation()
    {
    	$this->Module()->sCreateTranslationTable();
    	
    	$sql = 'SELECT COUNT(*) FROM `s_articles_translations`';
    	$count = Shopware()->Db()->fetchOne($sql);
    	
    	$this->assertEquals(20, $count);
    }
}