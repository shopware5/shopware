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

namespace Shopware\Tests\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Kernel;
use Zend_Http_Client;
use Zend_Http_Client_Adapter_Curl;
use Zend_Http_Client_Adapter_Exception;
use Zend_Json;

class VersionTest extends TestCase
{
    public $apiBaseUrl = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $helper = Shopware();

        $hostname = $helper->Shop()->getHost();
        if (empty($hostname)) {
            static::markTestSkipped(
                'Hostname is not available.'
            );
        }

        $this->apiBaseUrl = 'http://' . $hostname . $helper->Shop()->getBasePath() . '/api';
        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = ? WHERE username LIKE "demo"', [sha1('demo')]);
    }

    /**
     * @throws Zend_Http_Client_Adapter_Exception
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $username = 'demo';
        $password = sha1('demo');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig([
            'curloptions' => [
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
                CURLOPT_USERPWD => "$username:$password",
            ],
        ]);

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);

        return $client;
    }

    public function testGetVersionShouldBeSuccessful()
    {
        $kernel = new Kernel('testing', true);
        $release = $kernel->getRelease();

        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/version');
        $result = $client->request('GET');

        static::assertEquals('application/json', $result->getHeader('Content-Type'));
        static::assertEquals(null, $result->getHeader('Set-Cookie'));
        static::assertEquals(200, $result->getStatus());

        $result = $result->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);
        $data = $result['data'];
        static::assertInternalType('array', $data);

        static::assertEquals($release['version'], $data['version']);
        static::assertEquals($release['revision'], $data['revision']);
    }
}
