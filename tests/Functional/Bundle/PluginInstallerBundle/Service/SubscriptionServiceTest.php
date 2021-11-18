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

namespace Shopware\Tests\Functional\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLicenceService;
use Shopware\Bundle\PluginInstallerBundle\Service\SubscriptionService;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class SubscriptionServiceTest extends TestCase
{
    use ContainerTrait;

    private const TEST_SHOPWARE_VERSION = '5.7.7';

    public function testGetPluginInformationFromApi(): void
    {
        $storeClientMock = $this->createMock(StoreClient::class);
        $storeClientMock->expects(static::once())
            ->method('doPostRequest')
            ->with(
                static::anything(),
                static::callback(static function ($params) {
                    static::assertArrayHasKey('domain', $params);
                    static::assertArrayHasKey('shopwareVersion', $params);
                    static::assertSame(SubscriptionServiceTest::TEST_SHOPWARE_VERSION, $params['shopwareVersion']);
                    static::assertArrayHasKey('plugins', $params);

                    $firstPlugin = $params['plugins'][0];
                    static::assertIsArray($firstPlugin);
                    static::assertArrayHasKey('name', $firstPlugin);
                    static::assertArrayHasKey('version', $firstPlugin);
                    static::assertArrayHasKey('active', $firstPlugin);

                    return true;
                }),
                static::anything()
            )
            ->willReturn(['general' => ['upgradeRequired' => false, 'isUpgraded' => true], 'plugins' => []]);

        $resultStruct = $this->createService($storeClientMock)->getPluginInformationFromApi();

        static::assertTrue($resultStruct->IsShopUpgraded());
    }

    private function createService(StoreClient $storeClient): SubscriptionService
    {
        $connection = $this->getContainer()->get(Connection::class);
        $modelManager = $this->getContainer()->get(ModelManager::class);
        $pluginLicenceService = $this->createMock(PluginLicenceService::class);
        $release = new ShopwareReleaseStruct(self::TEST_SHOPWARE_VERSION, '', '1234567890');

        return new SubscriptionService(
            $connection,
            $storeClient,
            $modelManager,
            $pluginLicenceService,
            $release
        );
    }
}
