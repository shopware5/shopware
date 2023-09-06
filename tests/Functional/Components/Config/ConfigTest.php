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

namespace Shopware\Tests\Functional\Components\Config;

use Enlight_Components_Test_TestCase;
use Enlight_Config;
use Shopware\Components\Snippet\DbAdapter;

class ConfigTest extends Enlight_Components_Test_TestCase
{
    public function testMissingSnippetsConfigSection(): void
    {
        // Configure snippet database adapter
        $adapter = new DbAdapter([
            'sectionColumn' => ['shopID', 'localeID'],
        ]);

        // Simple default config object with section
        $config = new Enlight_Config([], [
            'section' => '1:1',
        ]);

        // Write multiple times
        $adapter->write($config);
        $adapter->write($config);
        $adapter->write($config);

        // Section should not be modified
        static::assertEquals('1:1', $config->getSection());
    }
}
