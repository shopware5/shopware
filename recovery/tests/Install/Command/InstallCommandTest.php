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

namespace Shopware\Recovery\Tests\Install\Command;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Shopware\Recovery\Common\IOHelper;
use Shopware\Recovery\Install\Command\InstallCommand;
use Shopware\Recovery\Tests\Traits\InstallCommandTestTrait;
use Symfony\Component\Console\Question\Question;

class InstallCommandTest extends TestCase
{
    use InstallCommandTestTrait;

    /**
     * @covers \Shopware\Recovery\Install\Command\InstallCommand::printStartMessage
     */
    public function testPrintStartMessage(): void
    {
        $ioHelper = static::createMock(IOHelper::class);
        $container = static::createMock(Container::class);

        $container->expects(static::once())
            ->method('offsetGet')
            ->with('shopware.version');

        $ioHelper->expects(static::exactly(2))
            ->method('cls');

        $ioHelper->expects(static::once())
            ->method('printBanner');

        $ioHelper->expects(static::exactly(2))
            ->method('writeln');

        $ioHelper->expects(static::once())
            ->method('ask')
            ->with(static::callback(static function (Question $question): bool {
                return $question->getDefault() === \PHP_EOL;
            }));

        $executeStartMessage = $this->initializeCommandAndCall($ioHelper, $container, 'printStartMessage');

        $executeStartMessage->call(new InstallCommand());
    }
}
