<?php

declare(strict_types=1);

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

namespace Shopware\Tests\Functional\Plugins\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Plugin_TestCase;
use Enlight_Controller_Response_Response;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Plugins_Frontend_Statistics_Bootstrap;

class StatisticsTest extends Enlight_Components_Test_Plugin_TestCase
{
    protected Shopware_Plugins_Frontend_Statistics_Bootstrap $plugin;

    /**
     * Test set up method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->plugin = Shopware()->Plugins()->Frontend()->Statistics();

        $sql = "INSERT IGNORE INTO `s_emarketing_partner` (`idcode`, `datum`, `company`, `contact`, `street`, `zipcode`, `city`, `phone`, `fax`, `country`, `email`, `web`, `profil`, `fix`, `percent`, `cookielifetime`, `active`, `userID`) VALUES
                  ('test123', '2010-01-01', 'Partner', '', '', '', '', '', '', '', '', '', '', 0, 10, 3600, 1, NULL)";
        Shopware()->Db()->query($sql);
    }

    /**
     * tear down the demo data
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $sql = "DELETE FROM s_emarketing_partner where idcode = 'test123'";
        Shopware()->Db()->query($sql);
    }

    /**
     * Retrieve plugin instance
     */
    public function Plugin(): Shopware_Plugins_Frontend_Statistics_Bootstrap
    {
        return $this->plugin;
    }

    /**
     * Test case method
     */
    public function testRefreshCurrentUsers(): void
    {
        $request = $this->Request();
        $request->setModuleName('frontend');
        $request->setDispatched(true);
        $request->setClientIp('192.168.33.10');
        $request->setRequestUri('/foobar');

        $request->setDeviceType('mobile');

        $this->Plugin()->refreshCurrentUsers($request);

        $sql = 'SELECT * FROM `s_statistics_currentusers` ORDER BY `id` DESC LIMIT 1';
        $result = Shopware()->Container()->get(Connection::class)->fetchAssociative($sql);
        static::assertIsArray($result);

        static::assertSame('192.168.0.0', $result['remoteaddr']); // IP should have been anonymized
        static::assertSame('/foobar', $result['page']);
        static::assertSame('mobile', $result['deviceType']);
    }

    /**
     * Referer provider
     *
     * @return array<array<mixed>>
     */
    public function providerReferer(): array
    {
        return [
          ['http://google.de/', '123', 'http://google.de/$123', true],
          ['http://google.de/', null, 'http://google.de/', true],
          ['http://google.de/', null, 'www.google.de/', false],
          ['http://google.de/', null, 'http://' . Shopware()->Config()->get('Host') . '/', false],
        ];
    }

    /**
     * Test case method
     *
     * @dataProvider providerReferer
     */
    public function testRefreshReferer(string $referer, ?string $partner, string $result, bool $assert): void
    {
        $request = $this->Request()->setQuery(['sPartner' => $partner, 'referer' => $referer]);

        $this->Plugin()->refreshReferer($request);

        $sql = 'SELECT `id` FROM `s_statistics_referer` WHERE `referer`=?';
        $insertId = Shopware()->Db()->fetchOne($sql, [$result]);

        static::assertSame($assert, !empty($insertId));
    }

    /**
     * Test case method
     */
    public function testRefreshPartner(): void
    {
        $request = $this->Request()->setParam('sPartner', 'test123');

        $response = $this->Response();

        $this->Plugin()->refreshPartner($request, $response);

        static::assertSame('test123', Shopware()->Session()->get('sPartner'));

        static::assertSame('test123', $this->getCookie($response));
    }

    /**
     * Test case method
     */
    public function testRefreshCampaign(): void
    {
        $request = $this->Request()
            ->setQuery('sPartner', 'sCampaign1');

        $response = $this->Response();

        $this->Plugin()->refreshPartner($request, $response);

        static::assertSame('sCampaign1', Shopware()->Session()->get('sPartner'));
    }

    public function testRefreshBlogWorksWithoutVisits(): void
    {
        $request = $this->Request()
            ->setQuery('blogId', 1);

        $plugin = $this->Plugin();

        $methode = TestReflectionHelper::getMethod(Shopware_Plugins_Frontend_Statistics_Bootstrap::class, 'refreshBlog');
        $methode->invoke($plugin, $request);

        static::assertSame([1], Shopware()->Session()->get('visitedBlogItems'));
    }

    private function getCookie(Enlight_Controller_Response_Response $response): ?string
    {
        foreach ($response->getCookies() as $cookie) {
            if ($cookie['name'] === 'partner') {
                return $cookie['value'];
            }
        }

        return null;
    }
}
