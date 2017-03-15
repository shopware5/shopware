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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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

        $sql = "INSERT IGNORE INTO `s_emarketing_partner` (`idcode`, `datum`, `company`, `contact`, `street`, `zipcode`, `city`, `phone`, `fax`, `country`, `email`, `web`, `profil`, `fix`, `percent`, `cookielifetime`, `active`, `userID`) VALUES
                  ('test123', '0000-00-00', 'Partner', '', '', '', '', '', '', '', '', '', '', 0, 10, 3600, 1, NULL)";
        Shopware()->Db()->query($sql);
    }

    /**
     * tear down the demo data
     */
    protected function tearDown()
    {
        parent::tearDown();

        $sql = "DELETE FROM s_emarketing_partner where idcode = 'test123'";
        Shopware()->Db()->query($sql);
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
    public function testRefreshCurrentUsers()
    {
        /** @var \Enlight_Controller_Request_RequestTestCase $request */
        $request = $this->Request()
            ->setModuleName('frontend')
            ->setDispatched(true)
            ->setRemoteAddress('192.168.33.10')
            ->setRequestUri('/foobar');

        /* @var \Enlight_Controller_Request_RequestTestCase $request */
        $request->setDeviceType('mobile');

        $this->Plugin()->refreshCurrentUsers($request);

        $sql = 'SELECT * FROM `s_statistics_currentusers` ORDER BY `id` DESC LIMIT 1';
        $result = Shopware()->Container()->get('dbal_connection')->fetchAssoc($sql);

        $this->assertSame('192.168.33.10', $result['remoteaddr']);
        $this->assertSame('/foobar', $result['page']);
        $this->assertSame('mobile', $result['deviceType']);
    }

    /**
     * Referer provider
     *
     * @return array
     */
    public function providerReferer()
    {
        return [
          ['http://google.de/', '123', 'http://google.de/$123', true],
          ['http://google.de/', null, 'http://google.de/', true],
          ['http://google.de/', null, 'www.google.de/', false],
          ['http://google.de/', null, 'http://' . Shopware()->Config()->Host . '/', false],
        ];
    }

    /**
     * Test case method
     *
     * @dataProvider providerReferer
     */
    public function testRefreshReferer($referer, $partner, $result, $assert)
    {
        $request = $this->Request()->setQuery(['sPartner' => $partner, 'referer' => $referer]);

        $tracer = Shopware()->Container()->get('shopware.statistics.tracer.referer_tracer');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();

        $tracer->trace($request, $context);

        $sql = 'SELECT `id` FROM `s_statistics_referer` WHERE `referer`=?';
        $insertId = Shopware()->Db()->fetchOne($sql, [
            $result,
        ]);

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
