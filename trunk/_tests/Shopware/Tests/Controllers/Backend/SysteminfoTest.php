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

class Shopware_Tests_Controllers_Backend_SysteminfoTest extends Enlight_Components_Test_Controller_TestCase
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

    public function testGetConfigList(){
        $response = $this->dispatch('backend/systeminfo/getConfigList');

        $this->assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('name', $jsonBody['data'][0]);
        $this->assertArrayHasKey('group', $jsonBody['data'][0]);
        $this->assertArrayHasKey('required', $jsonBody['data'][0]);
        $this->assertArrayHasKey('version', $jsonBody['data'][0]);
        $this->assertArrayHasKey('result', $jsonBody['data'][0]);
    }

    public function testGetPathList(){
        $response = $this->dispatch('backend/systeminfo/getPathList');

        $this->assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('name', $jsonBody['data'][0]);
        $this->assertArrayHasKey('version', $jsonBody['data'][0]);
        $this->assertArrayHasKey('result', $jsonBody['data'][0]);
    }

    public function testGetFileList(){
        $response = $this->dispatch('backend/systeminfo/getFileList');

        $this->assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('name', $jsonBody['data'][0]);
        $this->assertArrayHasKey('required', $jsonBody['data'][0]);
        $this->assertArrayHasKey('hash', $jsonBody['data'][0]);
        $this->assertArrayHasKey('version', $jsonBody['data'][0]);
        $this->assertArrayHasKey('result', $jsonBody['data'][0]);
    }

    public function testGetVersionList(){
        $response = $this->dispatch('backend/systeminfo/getVersionList');

        $this->assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('name', $jsonBody['data'][0]);
        $this->assertArrayHasKey('version', $jsonBody['data'][0]);
    }

    public function testGetEncoder(){
        $response = $this->dispatch('backend/systeminfo/getEncoder');

        $this->assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }
}