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

namespace Shopware\Tests\Functional\Controllers\Backend;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * @dataProvider formatSearchValueTestDataProvider
     */
    public function test_formatSearchValue(array $parameter, string $expectedResult): void
    {
        $controller = new ApplicationControllerMock();

        $method = (new \ReflectionClass(ApplicationControllerMock::class))->getMethod('formatSearchValue');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, $parameter);

        static::assertSame($expectedResult, $result);
    }

    public function formatSearchValueTestDataProvider(): array
    {
        return [
            [['', ['type' => '']], '%%'],
            [['test', ['type' => '']], '%test%'],
            [['12-12', ['type' => '']], '%12-12%'],
            [['12-12', ['type' => 'date']], '%12-12%'],
            [['12-12', ['type' => 'datetime']], '%12-12%'],
            [['2019-1016', ['type' => 'date']], '%2019-1016%'],
            [['2019-10-16', ['type' => 'datetime']], '%2019-10-16%'],
            [['2019-1016', ['type' => 'datetime']], '%2019-1016%'],
            [['23.06.1999', ['type' => 'datetime']], '%1999-06-23%'],
            [['23-1999', ['type' => 'date']], '%23-1999%'],
            [['23-1999', ['type' => 'datetime']], '%23-1999%'],
            [['2319-991', ['type' => 'datetime']], '%2319-991%'],
            [['2019-991', ['type' => 'date']], '%2019-991%'],
            [['2019-991', ['type' => 'datetime']], '%2019-991%'],
            [['2019-10-16', ['type' => 'date']], '2019-10-16'],
            [['23.06.1999', ['type' => 'date']], '1999-06-23'],
        ];
    }
}

class ApplicationControllerMock extends \Shopware_Controllers_Backend_Application
{
}
