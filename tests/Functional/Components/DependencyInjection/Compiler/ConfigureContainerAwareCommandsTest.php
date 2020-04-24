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

namespace Shopware\Tests\Functional\Components\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Application;

class ConfigureContainerAwareCommandsTest extends TestCase
{
    /**
     * @dataProvider getShopwareCommands
     */
    public function testCompilerAwareIsInitialized(ShopwareCommand $containerAwareCommand): void
    {
        static::assertNotNull($containerAwareCommand->getContainer());
    }

    public function getShopwareCommands(): array
    {
        /** @var Application $application */
        $application = Shopware()->Container()->get('application');
        $commands = [];

        foreach ($application->all() as $command) {
            if ($command instanceof ShopwareCommand) {
                $commands[] = [$command];
            }
        }

        return $commands;
    }
}
