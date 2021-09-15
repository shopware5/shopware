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

namespace Shopware\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Category\Repository;

class sCategoriesTest extends TestCase
{
    /**
     * @return array<array>
     */
    public function getData(): array
    {
        $baseURL = 'shopware.php?sViewport=cat&sCategory=';

        $testString = 'Hello. I am annoying';

        $testAssoc['id'] = 3;
        $testAssoc['name'] = 'TestProduct';
        $testAssoc['blog'] = false;
        $testAssoc['link'] = $baseURL . '3';

        $result1['id'] = 3;
        $result1['name'] = 'TestProduct';
        $result1['blog'] = false;
        $result1['link'] = $baseURL . '3';

        $result2 = [];

        return [
            [
                5,
                [$result1],
                [$testAssoc],
            ],
            [
                1,
                $result2,
                $testString,
            ],
        ];
    }

    /**
     * @dataProvider getData
     *
     * @param array<string, string|int|bool>|array[]               $expectedResult
     * @param array<string, array<string, string|int|bool>>|string $resultGiven
     */
    public function testGetCategoriesByParentArray(int $id, array $expectedResult, $resultGiven): void
    {
        $baseURL = 'shopware.php?sViewport=cat&sCategory=';

        $repository = $this->createMock(Repository::class);
        $sCategories = $this->createPartialMock(\sCategories::class, []);
        $sCategories->repository = $repository;
        $sCategories->baseUrl = $baseURL;
        $repository->method('getPathById')->willReturn($resultGiven);

        static::assertSame($expectedResult, $sCategories->sGetCategoriesByParent($id));
    }
}
