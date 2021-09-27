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

namespace Shopware\Tests\Functional\Components\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\LegacyPhpDumper;
use Shopware\Tests\Functional\Components\DependencyInjection\_fixtures\PrivateTestService;
use Shopware\Tests\Functional\Components\DependencyInjection\_fixtures\PublicTestService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use TestContainerLegacyPhpDumperTest;

class LegacyPhpDumperTest extends TestCase
{
    public function testGetServiceCallShouldNotFireEventForPrivateServices(): void
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load(__DIR__ . '/_fixtures/test_services.xml');

        $containerBuilder->compile();

        $dumper = new LegacyPhpDumper($containerBuilder);

        $tmpContainerFile = __DIR__ . '/TestContainerLegacyPhpDumperTest.php';

        $content = $dumper->dump(['class' => 'TestContainerLegacyPhpDumperTest', 'base_class' => Container::class]);

        file_put_contents($tmpContainerFile, $content);

        require_once __DIR__ . '/TestContainerLegacyPhpDumperTest.php';

        $testContainer = new TestContainerLegacyPhpDumperTest();

        $privateCalled = false;
        $testContainer->get('events')->addListener(sprintf('Enlight_Bootstrap_InitResource_%s', PrivateTestService::class), function () use (&$privateCalled) {
            $privateCalled = true;
        });

        $publicCalled = false;
        $testContainer->get('events')->addListener(sprintf('Enlight_Bootstrap_InitResource_%s', PublicTestService::class), function () use (&$publicCalled) {
            $publicCalled = true;
        });

        $testContainer->get(PublicTestService::class);

        unlink($tmpContainerFile);

        static::assertFalse($privateCalled);
        static::assertTrue($publicCalled);
    }
}
