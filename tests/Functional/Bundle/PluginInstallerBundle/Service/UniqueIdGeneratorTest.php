<?php declare(strict_types=1);
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

use PHPUnit\Framework\TestCase;

class UniqueIdGeneratorTest extends TestCase
{
    public function testUniqueIdGenerator(): void
    {
        Shopware()->Models()->getConnection()->executeUpdate('DELETE FROM s_core_config_elements WHERE `name` = "s_core_config_elements"');

        $uniqueService = Shopware()->Container()->get('shopware_plugininstaller.unique_id_generator');

        static::assertNotEmpty($uniqueService->getUniqueId());
    }
}
