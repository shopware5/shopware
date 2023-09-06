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

namespace Shopware\Tests\Functional\Models\Category;

use Enlight_Components_Test_TestCase;
use Shopware\Models\Category\Category;
use Shopware\Models\Category\Repository;

class PathByIdTest extends Enlight_Components_Test_TestCase
{
    protected ?Repository $repo = null;

    /**
     * @return array<array<int|array<int, string>>>
     */
    public function simpleNameArrayProvider(): array
    {
        return [
            [1, [1 => 'Root']],
            [3, [3 => 'Deutsch']],
            [39, [39 => 'English']],
            [6, [3 => 'Deutsch', 6 => 'Sommerwelten']],
            [11, [3 => 'Deutsch', 5 => 'Genusswelten', 11 => 'Tees und Zubehör']],
            [48, [39 => 'English', 43 => 'Worlds of indulgence', 47 => 'Teas and Accessories', 48 => 'Teas']],
        ];
    }

    /**
     * @return array<array<int|array<int, int>>>
     */
    public function simpleIdArrayProvider(): array
    {
        return [
            [1, [1 => 1]],
            [3, [3 => 3]],
            [39, [39 => 39]],
            [6, [3 => 3, 6 => 6]],
            [11, [3 => 3, 5 => 5, 11 => 11]],
            [48, [39 => 39, 43 => 43, 47 => 47, 48 => 48]],
        ];
    }

    /**
     * @return array[]
     */
    public function multiArrayProvider(): array
    {
        return [
            [1, [
                1 => ['id' => 1, 'name' => 'Root', 'blog' => false],
            ]],
            [3, [
                3 => ['id' => 3, 'name' => 'Deutsch', 'blog' => false],
            ]],
            [39, [
                39 => ['id' => 39, 'name' => 'English', 'blog' => false],
            ]],
            [5, [
                3 => ['id' => 3, 'name' => 'Deutsch', 'blog' => false],
                5 => ['id' => 5, 'name' => 'Genusswelten', 'blog' => false],
            ]],
            [48, [
                39 => ['id' => 39, 'name' => 'English', 'blog' => false],
                43 => ['id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false],
                47 => ['id' => 47, 'name' => 'Teas and Accessories', 'blog' => false],
                48 => ['id' => 48, 'name' => 'Teas', 'blog' => false],
            ]],
        ];
    }

    /**
     * @return array<array<string|int>>
     */
    public function stringPathProvider(): array
    {
        return [
            [1, 'Root'],
            [3, 'Deutsch'],
            [39, 'English'],
            [5, 'Deutsch > Genusswelten'],
            [12, 'Deutsch > Genusswelten > Tees und Zubehör > Tees'],
            [48, 'English > Worlds of indulgence > Teas and Accessories > Teas'],
        ];
    }

    /**
     * @dataProvider simpleNameArrayProvider
     *
     * @param array<int, string> $expectedResult
     */
    public function testGetPathByIdWithDefaultParameters(int $categoryId, array $expectedResult): void
    {
        $result = $this->getRepo()->getPathById($categoryId);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider simpleNameArrayProvider
     *
     * @param array<int, string> $expectedResult
     */
    public function testGetPathByIdWithDefaultNameParameter(int $categoryId, array $expectedResult): void
    {
        $result = $this->getRepo()->getPathById($categoryId, 'name');
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider simpleIdArrayProvider
     *
     * @param array<int, int> $expectedResult
     */
    public function testGetPathByIdWithIdParameter(int $categoryId, array $expectedResult): void
    {
        $result = $this->getRepo()->getPathById($categoryId, 'id');
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider multiArrayProvider
     *
     * @param array<int, array> $expectedResult
     */
    public function testGetPathByIdShouldReturnArray(int $categoryId, array $expectedResult): void
    {
        $result = $this->getRepo()->getPathById($categoryId, ['id', 'name', 'blog']);
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider stringPathProvider
     */
    public function testGetPathByIdShouldReturnPathAsString(int $categoryId, string $expectedResult): void
    {
        $result = $this->getRepo()->getPathById($categoryId, 'name', ' > ');
        static::assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider stringPathProvider
     */
    public function testGetPathByIdShouldReturnPathAsStringWithCustomSeparator(int $categoryId, string $expectedResult): void
    {
        $expectedResult = str_replace(' > ', '|', $expectedResult);

        $result = $this->getRepo()->getPathById($categoryId, 'name', '|');
        static::assertEquals($expectedResult, $result);
    }

    protected function getRepo(): Repository
    {
        if ($this->repo === null) {
            $this->repo = Shopware()->Models()->getRepository(Category::class);
        }

        return $this->repo;
    }
}
