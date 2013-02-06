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
 * @author     Benjamin Cremer
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
 * @group Mail
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_MailTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var array
     */
    public $testData = array(
        'name'        => 'Testmail123',
        'fromMail'    => 'Shopware Demoshop',
        'fromName'    => 'info@shopware.de',
        'subject'     => 'Test Email Subject',
        'content'     => 'Plaintext Content Example',
        'contentHtml' => 'HTML Context Example',
        'isHtml'      => true
    );

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

    public function testCreateMail()
    {
        $this->testData['name'] .= uniqid();

        $this->Request()->setMethod('POST')->setPost($this->testData);
        $response = $this->dispatch('/backend/mail/createMail');
        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        $this->assertEquals($this->testData['name'],        $result['name']);
        $this->assertEquals($this->testData['fromMail'],    $result['fromMail']);
        $this->assertEquals($this->testData['fromName'],    $result['fromName']);
        $this->assertEquals($this->testData['subject'],     $result['subject']);
        $this->assertEquals($this->testData['contentHtml'], $result['contentHtml']);
        $this->assertEquals($this->testData['isHtml'],      $result['isHtml']);

        $this->assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testCreateMail
     */
    public function testGetSingleMail($id)
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getMails?&node=NaN&id=' . $id);
        $body     = $response->getBody();
        $jsonBody = Zend_Json::decode($body);

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        $this->assertEquals($this->testData['fromMail'],    $result['fromMail']);
        $this->assertEquals($this->testData['fromName'],    $result['fromName']);
        $this->assertEquals($this->testData['subject'],     $result['subject']);
        $this->assertEquals($this->testData['contentHtml'], $result['contentHtml']);
        $this->assertEquals($this->testData['isHtml'],      $result['isHtml']);

        $this->assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testGetSingleMail
     */
    public function testUpdateMail($id)
    {
        $updateTestData = array(
            'subject' => 'foobar'
        );

        $this->Request()->setMethod('POST')->setPost($updateTestData);
        $response = $this->dispatch('/backend/mail/updateMail?id=' . $id);
        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        $this->assertEquals($updateTestData['subject'], $result['subject']);

        $this->assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testUpdateMail
     */
    public function testRemoveMail($id)
    {
        $response = $this->dispatch('/backend/mail/removeMail?id=' . $id);
        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);
    }


    public function testGetAttachmentsShouldBeSuccessful()
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getAttachments');
        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);
    }

    public function testGetMailsShouldBeSuccessful()
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getMails?&node=NaN');
        $jsonBody = Zend_Json::decode($response->getBody());

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertTrue($jsonBody['success']);
    }
}
