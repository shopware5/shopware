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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Controller;

use Symfony\Component\DomCrawler\Crawler;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AddressTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @return Crawler
     */
    private function doRequest($method = 'GET', $url, $data = [])
    {
        $this->reset();

        $this->Request()->setMethod($method);

        if ($method === 'POST') {
            $this->Request()->setPost($data);
        }

        $this->dispatch($url);

        if ($this->Response()->isRedirect()) {
            $parts = parse_url($this->Response()->getHeaders()[0]['value']);
            $followUrl = $parts['path'];
            return $this->doRequest('GET', $followUrl);
        }

        return new Crawler($this->Response()->getBody());
    }

    public function testList()
    {
        $this->ensureLogin();
        $crawler = $this->doRequest('GET', '/address/');

        $this->assertEquals(3, $crawler->filter('.address--item-content')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Standard-Rechnungsadresse")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Standard-Lieferadresse")')->count());
    }

    /**
     * @return int
     */
    public function testCreation()
    {
        $this->ensureLogin();
        $crawler = $this->doRequest(
            'POST',
            '/address/create/',
            [
                'address' => [
                    'salutation' => 'mr',
                    'firstname' => 'Luis',
                    'lastname' => 'King',
                    'street' => 'Fasanenstrasse 99',
                    'zipcode' => '79268',
                    'city' => 'Bötzingen',
                    'country' => 2
                ]
            ]
        );

        $this->assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich erstellt")')->count());
        $this->assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->count());
        $this->assertEquals(4, $crawler->filter('.address--item-content')->count());

        return (int) $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->filter('input[name=addressId]')->attr('value');
    }

    /**
     * @param $addressId
     * @depends testCreation
     */
    public function testEditPage($addressId)
    {
        $this->ensureLogin();

        // edit page
        $crawler = $this->doRequest('GET', '/address/edit/id/' . $addressId);
        $this->assertEquals('Fasanenstrasse 99', $crawler->filter('input[name="address[street]"]')->attr('value'));
    }

    /**
     * @param $addressId
     * @depends testCreation
     */
    public function testEdit($addressId)
    {
        $this->ensureLogin();

        // edit operation
        $crawler = $this->doRequest(
            'POST',
            '/address/edit/id/' . $addressId,
            [
                'address' => [
                    'salutation' => 'mr',
                    'firstname' => 'Joe',
                    'lastname' => 'Doe',
                    'street' => 'Fasanenstrasse 99',
                    'zipcode' => '79268',
                    'city' => 'Bötzingen',
                    'country' => 2
                ]
            ]
        );

        $this->assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich gespeichert")')->count());
        $this->assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Joe Doe")')->count());
        $this->assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->count());
        $this->assertEquals(4, $crawler->filter('.address--item-content')->count());
    }

    /**
     * @depends testCreation
     * @param int $addressId
     */
    public function testDeletion($addressId)
    {
        $this->ensureLogin();

        // delete confirm page
        $crawler = $this->doRequest('GET', '/address/delete/id/' . $addressId);
        $this->assertEquals(1, $crawler->filter('html:contains("Fasanenstrasse 99")')->count());

        // delete operation
        $crawler = $this->doRequest('POST', '/address/delete/id/' . $addressId, ['id' => $addressId]);
        $this->assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich gelöscht")')->count());
        $this->assertEquals(3, $crawler->filter('.address--item-content')->count());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The address is defined as default billing or shipping address and cannot be removed.
     */
    public function testDeletionOfDefaultAddressesShouldFail()
    {
        $this->ensureLogin();

        $this->doRequest('POST', '/address/delete/id/1/', ['id' => 1]);
    }

    /**
     * @depends testDeletionOfDefaultAddressesShouldFail
     */
    public function testVerifyAddressDeletionOfDefaultAddressesShouldFail()
    {
        $this->ensureLogin();

        $crawler = $this->doRequest('GET', '/address/');

        $this->assertEquals(3, $crawler->filter('.address--item-content')->count());
    }

    public function testChangeOfBillingAddressReflectsInAccount()
    {
        $this->ensureLogin();

        // crawl original data
        $crawler = $this->doRequest('GET', '/account');
        $originalText = trim($crawler->filter('.account--billing .panel--body p')->last()->text());
        $addressId = (int) $crawler->filter('.account--billing .panel--actions a:contains("oder andere Adresse wählen")')->attr('data-id');

        $this->assertGreaterThan(0, $addressId);

        // edit the entry
        $expectedText = str_replace('Max Mustermann', 'Shop Man', $originalText);
        $this->doRequest(
            'POST',
            '/address/edit/id/' . $addressId,
            [
                'address' => [
                    'salutation' => 'mr',
                    'company' => 'Muster GmbH',
                    'firstname' => 'Shop',
                    'lastname' => 'Man',
                    'street' => 'Musterstr. 55',
                    'zipcode' => '55555',
                    'city' => 'Musterhausen',
                    'country' => 2,
                    'state' => 3
                ]
            ]
        );

        // verify the changes
        $crawler = $this->doRequest('GET', '/account');
        $currentText = trim($crawler->filter('.account--billing .panel--body p')->last()->text());

        // reset to original
        $this->doRequest(
            'POST',
            '/address/edit/id/'.$addressId,
            [
                'address' => [
                    'salutation' => 'mr',
                    'company' => 'Muster GmbH',
                    'firstname' => 'Max',
                    'lastname' => 'Mustermann',
                    'street' => 'Musterstr. 55',
                    'zipcode' => '55555',
                    'city' => 'Musterhausen',
                    'state' => 3,
                    'country' => 2
                ]
            ]
        );

        $this->assertNotEquals($originalText, $currentText);
        $this->assertEquals($expectedText, $currentText);
    }

    /**
     * Log-in into account, needed for every test
     */
    private function ensureLogin()
    {
        $this->doRequest('POST', '/account/login', ['email' => 'test@example.com', 'password' => 'shopware']);
    }
}
