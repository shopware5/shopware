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
 * @author     Heiner Lohaus
 * @author     $Author$
 */

class Shopware_Tests_Controllers_Backend_LogTest extends Enlight_Components_Test_Controller_TestCase
{
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
     * Tests the getLogsAction()
     * to test if reading the logs is working
     */
    public function testGetLogs(){
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/log/getLogs');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('total', $jsonBody);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test tests the creating of a new log.
     * This function is called before testDeleteLogs
     * @return mixed
     */
    public function testCreateLog(){

        $this->Request()->setMethod('POST')->setPost(
            array(
                'type'   => 'backend',
                'key'    => 'Log',
                'text'   => 'DummyText',
                'date'   => new \DateTime('now'),
                'user'   => 'Administrator',
                'value4' => ''
            )
        );

        $this->dispatch('backend/log/createLog');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('id', $jsonBody['data']);

        return $jsonBody['data']['id'];
    }

    /**
     * This test-method tests the deleting of a log.
     *
     * @depends testCreateLog
     * @param $lastId
     */
    public function testDeleteLogs($lastId){
        $this->Request()->setMethod('POST')->setPost(array('id'=>$lastId));

        $this->dispatch('backend/log/deleteLogs');

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('data', $jsonBody);
    }
}
