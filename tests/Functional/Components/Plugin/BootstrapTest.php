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

namespace Shopware\Tests\Functional\Components\Plugin;

use Enlight_Config;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Components\Plugin\fixtures\TestPlugin;

class BootstrapTest extends TestCase
{
    public function testInstantiation(): void
    {
        $expectedPluginName = 'Test';
        $pluginInstance = new TestPlugin($expectedPluginName);
        $pluginName = $pluginInstance->getName();
        static::assertSame($expectedPluginName, $pluginName);
    }

    /**
     * @dataProvider pluginVersionProvider
     */
    public function testHasNewerVersion(?string $currentVersion, ?string $updateVersion, bool $isNewer): void
    {
        $currentPluginConfig = new Enlight_Config(['version' => $currentVersion]);
        $updatePluginConfig = new Enlight_Config(['version' => $updateVersion]);

        $pluginBootstrap = new TestPlugin('test');

        static::assertSame($isNewer, $pluginBootstrap->hasInfoNewerVersion($updatePluginConfig, $currentPluginConfig));
    }

    /**
     * @return list<array{currentVersion: ?string, updateVersion: ?string, isNewer: bool}>
     */
    public function pluginVersionProvider(): array
    {
        return [
            [
                'currentVersion' => '2.0.0',
                'updateVersion' => '2.0.0',
                'isNewer' => false,
            ],
            [
                'currentVersion' => '1.0.0',
                'updateVersion' => '2.0.0',
                'isNewer' => true,
            ],
            [
                'currentVersion' => null,
                'updateVersion' => '2.0.0',
                'isNewer' => true,
            ],
            [
                'currentVersion' => '2.0.0',
                'updateVersion' => null,
                'isNewer' => false,
            ],
            [
                'currentVersion' => '1',
                'updateVersion' => '1.0.0',
                'isNewer' => false,
            ],
            [
                'currentVersion' => '0',
                'updateVersion' => '0.0.1',
                'isNewer' => true,
            ],
            [
                'currentVersion' => '0',
                'updateVersion' => '0',
                'isNewer' => false,
            ],
        ];
    }
}
