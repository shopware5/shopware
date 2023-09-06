<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\HttpCache;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\ConfigWriter;
use Shopware\Components\HttpCache\CacheRouteInstaller;

class CacheRouteInstallerTest extends TestCase
{
    private Connection $connection;

    private CacheRouteInstaller $cacheRouteInstaller;

    private ConfigWriter $configWriter;

    /**
     * @before
     */
    public function startTransaction(): void
    {
        $this->connection = Shopware()->Container()->get(Connection::class);
        $this->cacheRouteInstaller = Shopware()->Container()->get('shopware.http_cache.route_installer');
        $this->configWriter = Shopware()->Container()->get(ConfigWriter::class);

        $this->connection->beginTransaction();
    }

    /**
     * @after
     */
    public function rollBackTransaction(): void
    {
        $this->connection->rollBack();
    }

    public function testAddHttpCacheRoute(): void
    {
        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400, ['price']);

        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');
        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');

        static::assertTrue($result);
        static::assertStringContainsString('widgets/swag_emotion_test 14400', $cacheRoutes);
        static::assertStringContainsString('widgets/swag_emotion_test price', $noCacheRoutes);
    }

    public function testAddHttpCacheRouteWithoutTag(): void
    {
        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);

        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');
        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');

        static::assertTrue($result);
        static::assertStringContainsString('widgets/swag_emotion_test 14400', $cacheRoutes);
        static::assertStringNotContainsString('widgets/swag_emotion_test price', $noCacheRoutes);
    }

    public function testAddHttpCacheRouteEmptyRoutes(): void
    {
        $sql = "UPDATE `s_core_config_elements` SET `value` = NULL WHERE `s_core_config_elements`.`name` = 'cacheControllers';";
        $this->connection->executeQuery($sql);

        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);

        static::assertFalse($result);
    }

    public function testAddHttpCacheRouteAlreadyExists(): void
    {
        $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);
        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);

        static::assertTrue($result);
    }

    public function testAddHttpCacheRouteAlreadyExistsButDifferentTime(): void
    {
        $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);
        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 9999);

        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');

        static::assertTrue($result);
        static::assertStringContainsString('widgets/swag_emotion_test 9999', $cacheRoutes);
    }

    public function testAddHttpCacheRouteCacheTagAlreadyExists(): void
    {
        $result1 = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400, ['price']);
        $result2 = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400, ['price']);
        $result3 = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400, ['foo']);

        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');

        static::assertTrue($result1 && $result2 && $result3);
        static::assertStringContainsString('widgets/swag_emotion_test price', $noCacheRoutes);
        static::assertStringContainsString('widgets/swag_emotion_test foo', $noCacheRoutes);
    }

    public function testAddHttpCacheRouteEmptyRouteReturnsNull(): void
    {
        $this->cacheRouteInstaller->addHttpCacheRoute("\n", 9999);
        $result = $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);

        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');

        static::assertTrue($result);
        static::assertStringContainsString('widgets/swag_emotion_test 14400', $cacheRoutes);
        static::assertStringNotContainsString('9999', $cacheRoutes);
    }

    public function testRemoveHttpCacheRoute(): void
    {
        $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_test', 14400);
        $result = $this->cacheRouteInstaller->removeHttpCacheRoute('widgets/swag_emotion_test');

        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');
        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');

        static::assertTrue($result);
        static::assertStringNotContainsString('widgets/swag_emotion_test 14400', $cacheRoutes);
        static::assertStringNotContainsString('widgets/swag_emotion_test price', $noCacheRoutes);
    }

    public function testRemoveHttpCacheRouteEmptyRoutes(): void
    {
        $sql = "UPDATE `s_core_config_elements` SET `value` = NULL WHERE `s_core_config_elements`.`name` = 'cacheControllers';";
        $this->connection->executeQuery($sql);

        $result = $this->cacheRouteInstaller->removeHttpCacheRoute('widgets/swag_emotion_test');

        static::assertFalse($result);
    }
}
