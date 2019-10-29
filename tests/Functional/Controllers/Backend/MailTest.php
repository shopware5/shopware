<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

class MailTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var array
     */
    public $testData = [
        'name' => 'Testmail123',
        'fromMail' => 'Shopware Demoshop',
        'fromName' => 'info@example.com',
        'subject' => 'Test Email Subject',
        'content' => 'Plaintext Content Example',
        'contentHtml' => 'HTML Context Example',
        'isHtml' => true,
    ];

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testCreateMail()
    {
        $this->testData['name'] .= uniqid(rand());

        $this->Request()->setMethod('POST')->setPost($this->testData);
        $response = $this->dispatch('/backend/mail/createMail');
        $jsonBody = \json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        static::assertEquals($this->testData['name'], $result['name']);
        static::assertEquals($this->testData['fromMail'], $result['fromMail']);
        static::assertEquals($this->testData['fromName'], $result['fromName']);
        static::assertEquals($this->testData['subject'], $result['subject']);
        static::assertEquals($this->testData['contentHtml'], $result['contentHtml']);
        static::assertEquals($this->testData['isHtml'], $result['isHtml']);

        static::assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testCreateMail
     */
    public function testGetSingleMail($id)
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getMails?&node=NaN&id=' . $id);
        $body = $response->getBody();
        $jsonBody = \json_decode($body, true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        static::assertEquals($this->testData['fromMail'], $result['fromMail']);
        static::assertEquals($this->testData['fromName'], $result['fromName']);
        static::assertEquals($this->testData['subject'], $result['subject']);
        static::assertEquals($this->testData['contentHtml'], $result['contentHtml']);
        static::assertEquals($this->testData['isHtml'], $result['isHtml']);

        static::assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testGetSingleMail
     */
    public function testUpdateMail($id)
    {
        $updateTestData = [
            'subject' => 'foobar',
        ];

        $this->Request()->setMethod('POST')->setPost($updateTestData);
        $response = $this->dispatch('/backend/mail/updateMail?id=' . $id);
        $jsonBody = \json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);

        $result = $jsonBody['data'];

        static::assertEquals($updateTestData['subject'], $result['subject']);

        static::assertArrayHasKey('id', $result);

        return $result['id'];
    }

    /**
     * @depends testUpdateMail
     */
    public function testRemoveMail($id)
    {
        $response = $this->dispatch('/backend/mail/removeMail?id=' . $id);
        $jsonBody = \json_decode($response->getBody(), true);

        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);
    }

    public function testGetAttachmentsShouldBeSuccessful()
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getAttachments');
        $jsonBody = \json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);
    }

    public function testGetMailsShouldBeSuccessful()
    {
        $this->Request()->setMethod('GET');

        $response = $this->dispatch('/backend/mail/getMails?&node=NaN');
        $jsonBody = \json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);
    }
}
