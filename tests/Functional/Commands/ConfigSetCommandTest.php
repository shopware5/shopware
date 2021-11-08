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

namespace Shopware\Tests\Functional\Commands;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Commands\ConfigSetCommand;
use Shopware\Models\Config\Element as ConfigElement;
use Shopware\Models\Config\Value as ConfigValue;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigSetCommandTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    /**
     * @dataProvider configValueProvider
     *
     * @param bool|float|int|string|null $expectedValue
     */
    public function testExecute(string $configName, string $value, $expectedValue, bool $decode): void
    {
        $command = $this->getContainer()->get(ConfigSetCommand::class);
        $commandTester = new CommandTester($command);

        $input = [
            'name' => $configName,
            'value' => $value,
        ];
        if ($decode) {
            $input['--decode'] = null;
        }
        $commandTester->execute($input);

        static::assertSame(0, $commandTester->getStatusCode());

        $element = $this->getContainer()->get('models')->getRepository(ConfigElement::class)->findOneBy(['name' => $configName]);
        static::assertInstanceOf(ConfigElement::class, $element);

        $value = $this->getContainer()->get('models')->getRepository(ConfigValue::class)->findOneBy(['element' => $element->getId()]);
        static::assertInstanceOf(ConfigValue::class, $value);

        static::assertSame($expectedValue, $value->getValue());
    }

    /**
     * @return Generator<string, array>
     */
    public function configValueProvider(): Generator
    {
        yield 'Decode boolean false value' => ['show_cookie_note', 'false', false, true];
        yield 'Decode boolean true value' => ['show_cookie_note', 'true', true, true];
        yield 'Decode integer value' => ['show_cookie_note', '12', 12, true];
        yield 'Decode float value' => ['show_cookie_note', '1.2', 1.2, true];
        yield 'Decode null value' => ['show_cookie_note', 'null', null, true];
        yield 'String value' => ['show_cookie_note', 'test', 'test', false];
        yield "Decode string value. Don't do that" => ['show_cookie_note', 'test', null, true];
    }
}
