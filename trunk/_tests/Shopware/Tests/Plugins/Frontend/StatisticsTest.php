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
class Shopware_Tests_Plugins_Frontend_StatisticsTest extends Enlight_Components_Test_Plugin_TestCase
{
	/**
     * @var Shopware_Plugins_Frontend_Paypal_Bootstrap
     */
    protected $plugin;
    
    /**
     * Test set up method
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->plugin = Shopware()->Plugins()->Frontend()->Statistics();
    }
    
    /**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {       
    	return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Statistic').'Log.xml');
    }
    
	/**
	 * Retrieve plugin instance
	 *
	 * @return Shopware_Plugins_Frontend_Statistics_Bootstrap
	 */
	public function Plugin()
	{
		return $this->plugin;
	}
    
    /**
     * Test case method
     */
    public function testDispatchLoopShutdown()
    {
    	$request = $this->Request()
			->setModuleName('frontend')
			->setDispatched(true)
			->setClientIp('127.0.0.1', false)
			->setRequestUri('/');
			
		$response = $this->Response();
    		
    	$action = $this->getMock('Enlight_Controller_Action',
    		null,
    		array($request, $response)
    	);
    	
    	Shopware()->Session()->Bot = false;
    	Shopware()->Config()->BlockIP = null;
    		
    	$eventArgs = new Enlight_Controller_EventArgs(array(
            'subject' => $action,
            'request' => $request,
            'response' => $response,
        ));
    	
    	$e = null;
   		try { 
    		$this->Plugin()->onDispatchLoopShutdown($eventArgs);
   		} catch (Exception $e) { }
   		
   		$this->assertEquals(null, $e);
    }
    
    /**
     * Test case method
     */
    public function testRefreshCurrentUsers()
    {
    	$request = $this->Request()
			->setModuleName('frontend')
			->setDispatched(true)
			->setClientIp('127.0.0.1', false)
			->setRequestUri('/');
    	    	    	
    	$this->Plugin()->refreshCurrentUsers($request);
    	
    	$sql = 'SELECT `id` FROM `s_statistics_currentusers` WHERE `remoteaddr`=? AND `page`=?';
    	$insertId = Shopware()->Db()->fetchOne($sql, array(
    		$request->getClientIp(false),
    		$request->getRequestUri()
    	));
    	
    	$this->assertNotEmpty($insertId);
    }
    
    /**
     * Referer provider
     *
     * @return unknown
     */
    public function providerReferer()
    {
        return array(
          array('http://google.de/', '123', 'http://google.de/$123', true),
          array('http://google.de/', null, 'http://google.de/', true),
          array('http://google.de/', null, 'www.google.de/', false),
          array('http://google.de/', null, 'http://'.Shopware()->Config()->Host.'/', false)
        );
    }
    
    /**
     * Test case method
     * 
     * @dataProvider providerReferer
     */
    public function testRefreshReferer($referer, $partner, $result, $assert)
    {
    	$request = $this->Request()
			->setHeader('REFERER', $referer)
			->setQuery('sPartner', $partner);
    	    	    	
    	$this->Plugin()->refreshReferer($request);
    	
    	$sql = 'SELECT `id` FROM `s_statistics_referer` WHERE `referer`=?';
    	$insertId = Shopware()->Db()->fetchOne($sql, array(
    		$result
    	));
    	
    	$this->assertEquals($assert, !empty($insertId));
    }
    
    /**
     * Test case method
     */
    public function testRefreshPartner()
    {
    	$request = $this->Request()
			->setParam('sPartner', 'test123');
			
		$response = $this->Response();
    	    	    	
    	$this->Plugin()->refreshPartner($request, $response);
    	
    	$this->assertEquals('test123', Shopware()->Session()->sPartner);
    	$this->assertEquals('test123', $response->getCookie('partner'));
    }
    
    /**
     * Test case method
     */
    public function testRefreshCampaign()
    {
    	$request = $this->Request()
			->setQuery('sPartner', 'sCampaign1');
			
		$response = $this->Response();
    	    	    	
    	$this->Plugin()->refreshPartner($request, $response);
    	
    	$this->assertEquals('sCampaign1', Shopware()->Session()->sPartner);
    }
}