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
class Shopware_Tests_Plugins_Frontend_GoogleTest extends Enlight_Components_Test_Plugin_TestCase
{
	/**
     * @var Shopware_Plugins_Frontend_Google_Bootstrap
     */
    protected $plugin;
    
    /**
     * Test set up method
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->plugin = Shopware()->Plugins()->Frontend()->Google();
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
     * 
     * @ticket 5268
     */
    public function testPostDispatch()
    {
    	$request = $this->Request()
			->setModuleName('frontend')
			->setDispatched(true);
			
		$response = $this->Response();
			
    	$this->Plugin()->Config()
    		->setAllowModifications()
    		->set('tracking_code', 'TEST1234')
    		->set('anonymize_ip', true);

        $view = new Enlight_View_Default(
            Shopware()->Template()
        );
        $view->loadTemplate('frontend/index/index.tpl');
    		
    	$action = $this->getMock('Enlight_Controller_Action',
    		null,
    		array($request, $response)
    	);

        $action->setView($view);
    		
    	$eventArgs = $this->createEventArgs()
    		->setSubject($action);
    		
    	$e = null;
   		try { 
    		$this->Plugin()->onPostDispatch($eventArgs);
   		} catch (Exception $e) { }
   		
   		$this->assertNull($e);
   		$this->assertEquals('TEST1234', $view->GoogleTrackingID);
    	$this->assertTrue($view->GoogleAnonymizeIp);
    	
    	$this->assertContains(
    		'frontend/plugins/google/index.tpl',
            $view->Template()->getTemplateResource()
    	);
    }
}