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

class BlogCategoryTreeListQueryTest extends Enlight_Components_Test_TestCase
{
    protected ?Repository $repo = null;

    /**
     * @var array<int, array>
     */
    protected array $expected = [
        1 => [
            0 => [
                'id' => 3,
                'name' => 'Deutsch',
                'position' => 0,
                'blog' => false,
                'childrenCount' => '1',
            ],
            1 => [
                'id' => 39,
                'name' => 'English',
                'position' => 1,
                'blog' => false,
                'childrenCount' => '1',
            ],
        ],
        3 => [
            0 => [
                'id' => 17,
                'name' => 'Trends + News',
                'position' => 5,
                'blog' => true,
                'childrenCount' => '0',
            ],
        ],
        39 => [
            0 => [
                'id' => 42,
                'name' => 'Trends + News',
                'position' => 0,
                'blog' => true,
                'childrenCount' => '0',
            ],
        ],
    ];

    public function testQuery(): void
    {
        foreach ($this->expected as $id => $expected) {
            $filter = [['property' => 'c.parentId', 'value' => $id]];
            $query = $this->getRepo()->getBlogCategoryTreeListQuery($filter);
            $data = $this->removeDates($query->getArrayResult());
            static::assertEquals($expected, $data);
        }
    }

    protected function getRepo(): Repository
    {
        if ($this->repo === null) {
            $this->repo = Shopware()->Models()->getRepository(Category::class);
        }

        return $this->repo;
    }

    /**
     * @param array<array<string, mixed>> $data
     *
     * @return array<array<string, mixed>>
     */
    protected function removeDates(array $data): array
    {
        foreach ($data as &$subCategory) {
            unset($subCategory['changed'], $subCategory['cmsText'], $subCategory['added']);
            if (isset($subCategory['emotions'])) {
                foreach ($subCategory['emotions'] as &$emotion) {
                    unset($emotion['createDate'], $emotion['modified']);
                }
            }
            unset($emotion);

            if (isset($subCategory['articles'])) {
                foreach ($subCategory['articles'] as &$article) {
                    unset($article['added'], $article['changed'], $article['mainDetail']['releaseDate']);
                }
            }
        }

        return $data;
    }
}
