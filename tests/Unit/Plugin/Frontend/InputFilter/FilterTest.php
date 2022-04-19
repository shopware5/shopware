<?php
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

namespace Shopware\Tests\Unit\Plugin\Frontend\InputFilter;

use PHPUnit\Framework\TestCase;
use Shopware_Plugins_Frontend_InputFilter_Bootstrap;

class FilterTest extends TestCase
{
    private Shopware_Plugins_Frontend_InputFilter_Bootstrap $inputFilter;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->inputFilter = $this->createMock(Shopware_Plugins_Frontend_InputFilter_Bootstrap::class);
    }

    public function sqlProvider(): array
    {
        return [
            ['SELECT * FROM s_core_auth'],
            ['SELECT * FROM s_order_details'],
            ['SELECT * FROM benchmark.foo'],
            ["INSERT INTO foo (bar) VALUES ('moo')"],
            ["REPLACE INSERT INTO foo (bar) VALUES ('moo')"],
            ["REPLACE INTO foo (bar) VALUES ('moo')"],
            ['UPDATE foo SET a=2 WHERE x=y'],
            ['DELETE FROM foo WHERE id > 1'],
            ['ALTER TABLE foo ADD COLUMN bar int(1)'],
            ['RENAME TABLE foo TO foobar'],
            ['CREATE TABLE foobar (id int(11))'],
            ['DROP TABLE foobar'],
            ['TRUNCATE TABLE foobar'],
            ['ALTER DATABASE `shopware` UPGRADE DATA DIRECTORY NAME;'],
            ['RENAME DATABASE shopware TO shopware_foo'],
            ['SELECT * FROM s_user UNION ALL SELECT * FROM s_user_addresses'],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO   OUTFILE '/var/www/backdoor.php'"],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO   OUTFILE '/var/www/backdoor.php'"],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO OUTFILE '/var/www/backdoor.php'"],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO   DUMPFILE '/var/www/backdoor.php'"],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO   DUMPFILE '/var/www/backdoor.php'"],
            ["SELECT CONCAT(CHAR(60),CHAR(63),CHAR(112),CHAR(104),CHAR(112),CHAR(32),CHAR(115),CHAR(121),CHAR(115),CHAR(116),CHAR(101),CHAR(109),CHAR(40),CHAR(36),CHAR(95),CHAR(71),CHAR(69),CHAR(84),CHAR(91),CHAR(39),CHAR(99),CHAR(111),CHAR(109),CHAR(109),CHAR(97),CHAR(110),CHAR(100),CHAR(39),CHAR(93),CHAR(41),CHAR(59),CHAR(32),CHAR(63),CHAR(62)) INTO DUMPFILE '/var/www/backdoor.php'"],
        ];
    }

    public function striptagsDataProvider(): array
    {
        return [
            [
                '<foo',
                [
                    'enabled' => '',
                    'disabled' => '<foo',
                ],
            ],
            [
                'The rest will be cut <foo',
                [
                    'enabled' => 'The rest will be cut ',
                    'disabled' => 'The rest will be cut <foo',
                ],
            ],
            [
                'This should not < be touched',
                [
                    'enabled' => 'This should not < be touched',
                    'disabled' => 'This should not < be touched',
                ],
            ],
            [
                'This should <be> touched',
                [
                    'enabled' => 'This should  touched',
                    'disabled' => 'This should <be> touched',
                ],
            ],
        ];
    }

    /**
     * @dataProvider sqlProvider
     */
    public function testSql(string $statement): void
    {
        $regex = '#' . $this->inputFilter->sqlRegex . '#msi';
        $statement = Shopware_Plugins_Frontend_InputFilter_Bootstrap::filterValue($statement, $regex);

        static::assertNull($statement);
    }

    /**
     * @dataProvider striptagsDataProvider
     */
    public function testStripTagsEnabled(string $input, array $expected): void
    {
        static::assertEquals(
            $expected['enabled'],
            Shopware_Plugins_Frontend_InputFilter_Bootstrap::filterValue($input, '#PreventRegexMatch#', true)
        );
    }

    /**
     * @dataProvider striptagsDataProvider
     */
    public function testStripTagsDisabled(string $input, array $expected): void
    {
        static::assertEquals(
            $expected['disabled'],
            Shopware_Plugins_Frontend_InputFilter_Bootstrap::filterValue($input, '#PreventRegexMatch#', false)
        );
    }

    /**
     * @dataProvider stripxssDataProvider
     */
    public function testXssFilter(string $input, ?string $expected): void
    {
        $result = Shopware_Plugins_Frontend_InputFilter_Bootstrap::filterValue($input, '#' . $this->inputFilter->xssRegex . '#msi');

        static::assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @dataProvider stripxssArrayDataProvider
     *
     * @param array<mixed, mixed>  $input
     * @param ?array<mixed, mixed> $expected
     */
    public function testXssFilterOnArray(array $input, ?array $expected, bool $stripTag): void
    {
        $result = Shopware_Plugins_Frontend_InputFilter_Bootstrap::filterArrayValue($input, '#' . $this->inputFilter->xssRegex . '#msi', $stripTag);

        static::assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function stripxssDataProvider(): array
    {
        return [
            [
                'input' => 'data-foo', // Input value
                'expected' => null, // Expected result
            ],
            [
                'input' => 'data-foo="bar"',
                'expected' => null,
            ],
            [
                'input' => 'data-dosomething ',
                'expected' => null,
            ],
            [
                'input' => 'foo bar\'hallo welt" data-dosomething foo bar',
                'expected' => null,
            ],
            [
                'input' => 'someone@data-foo.com',
                'expected' => 'someone@data-foo.com',
            ],
            [
                'input' => 'foo bar jemand@data-foo.com foo bar',
                'expected' => 'foo bar jemand@data-foo.com foo bar',
            ],
            [
                'input' => 'foo barfoo bar data-dosomething="aweful" foo bar',
                'expected' => null,
            ],
            [
                'input' => 'foo bar data-dosomething',
                'expected' => null,
            ],
            [
                'input' => '      data-dosomething   ',
                'expected' => null,
            ],
            [
                'input' => 'data-dosomething   ',
                'expected' => null,
            ],
            [
                'input' => 'foodata-dosomething   ',
                'expected' => 'foodata-dosomething   ',
            ],
            [
                'input' => 'foo bar jemand@data-foo.com foo bar',
                'expected' => 'foo bar jemand@data-foo.com foo bar',
            ],
            [
                'input' => 'assdsa jemand@data-foo.com',
                'expected' => 'assdsa jemand@data-foo.com',
            ],
            [
                'input' => ' jemand@fara-data-foo.com  ',
                'expected' => ' jemand@fara-data-foo.com  ',
            ],
            [
                'input' => 'jemand@fara-data-foo.com',
                'expected' => 'jemand@fara-data-foo.com',
            ],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function stripxssArrayDataProvider(): array
    {
        return [
            [
                'input' => [
                    'sessionKey' => 'checkoutBillingAddressId,checkoutShippingAddressId',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'expected' => [
                    'sessionKey' => 'checkoutBillingAddressId,checkoutShippingAddressId',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'stripTag' => true,
            ],
            [
                'input' => [
                    "\"><svg onload=\"prompt('Hey Shopware!')\">" => 'checkoutBillingAddressId,checkoutShippingAddressId',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'expected' => [
                    '">' => 'checkoutBillingAddressId,checkoutShippingAddressId',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'stripTag' => true,
            ],
            [
                'input' => [
                    'sessionKey' => "\"><svg onload=\"prompt('Hey Shopware!')\">",
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'expected' => [
                    'sessionKey' => '">',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'stripTag' => true,
            ],
            [
                'input' => [
                    "\"><svg onload=\"prompt('Hey Shopware!')\">" => 'checkoutBillingAddressId,checkoutShippingAddressId',
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'expected' => [
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'stripTag' => false,
            ],
            [
                'input' => [
                    'sessionKey' => "\"><svg onload=\"prompt('Hey Shopware!')\">",
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'expected' => [
                    'sessionKey' => null,
                    'setDefaultBillingAddress' => '',
                    'setDefaultShippingAddress' => 'fooo',
                ],
                'stripTag' => false,
            ],
            [
                'input' => [
                    'multiArray' => [
                        'innerArray' => "><svg onload=\"prompt('Hey Shopware!')\">",
                    ],
                ],
                'expected' => [
                    'multiArray' => [
                        'innerArray' => null,
                    ],
                ],
                'stripTag' => false,
            ],
        ];
    }
}
