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
use Shopware\Bundle\PluginInstallerBundle\Context\BaseRequest;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLocalService;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class PluginLocalServiceTest extends TestCase
{
    use ContainerTrait;

    public function testGetPlugins(): void
    {
        $installerService = $this->createMock(InstallerService::class);
        $installerService->method('getPluginPath')->willReturn('plugin/path');
        $pluginLocalService = new PluginLocalService(
            $this->getContainer()->get(Connection::class),
            $this->getContainer()->get(StructHydrator::class),
            $this->getContainer()->getParameter('shopware.app.rootDir'),
            $installerService,
            $this->getContainer()->get('front')
        );

        $iteratePluginsMethod = (new \ReflectionClass(PluginLocalService::class))->getMethod('iteratePlugins');
        $iteratePluginsMethod->setAccessible(true);

        $pluginData = [
            [
                'name' => 'SwagTest',
                'changes' => json_encode([
                    '1.1.0' => [
                        'de' => [
                            0 => '',
                        ],
                        'en' => [
                            0 => '',
                        ],
                    ],
                    '1.0.0' => [
                        'de' => [
                            0 => 'Erstveröffentlichung',
                        ],
                        'en' => [
                            0 => 'First release',
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
            ],
        ];

        $context = new BaseRequest('en', '5.7.3');
        $pluginStructs = $iteratePluginsMethod->invoke($pluginLocalService, $pluginData, $context);

        $pluginStruct = $pluginStructs['swagtest'];
        static::assertInstanceOf(PluginStruct::class, $pluginStruct);

        $changelog = $pluginStruct->getChangelog();
        static::assertSame('1.1.0', $changelog[0]['version']);
        static::assertSame('', $changelog[0]['text']);
        static::assertSame('1.0.0', $changelog[1]['version']);
        static::assertStringContainsString('First release', $changelog[1]['text']);
    }
}
