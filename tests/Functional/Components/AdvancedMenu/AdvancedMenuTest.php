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

namespace Shopware\Tests\Functional\Components\AdvancedMenu;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;

class AdvancedMenuTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $mainCategoryId;

    public function setUp()
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        $this->connection->insert('s_categories', [
            'parent' => 1,
            'description' => 'MainCategory',
            'active' => 1,
        ]);
        $this->mainCategoryId = $this->connection->lastInsertId('s_categories');

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->connection->rollBack();

        parent::tearDown();
    }

    public function testGetAdvancedMenu()
    {
        $reader = Shopware()->Container()->get('shopware_storefront.advanced_menu_reader');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $categories = [
            [
                'name' => 'first level',
                'sub' => [
                    ['name' => 'second level'],
                    ['name' => 'third level'],
                    [
                        'name' => 'fourth level',
                        'sub' => [
                            ['name' => 'fourth sub level'],
                            ['name' => 'fourth sub level 02'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'first level 02',
                'sub' => [
                    ['name' => 'second level 02'],
                    ['name' => 'third level 02'],
                    ['name' => 'fourth level 02'],
                ],
            ],
        ];

        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get($this->mainCategoryId, $context);
        $context->getShop()->setCategory($category);

        $this->saveTree($categories, [$this->mainCategoryId]);

        $menu = $reader->get($context, 10);

        $menu = $this->extractTree($menu);

        $this->assertSame($categories, $menu);
    }

    public function testGetAdvancedMenuFromSubcategory()
    {
        $reader = Shopware()->Container()->get('shopware_storefront.advanced_menu_reader');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $categories = [
            [
                'name' => 'first level',
                'sub' => [
                    ['name' => 'second level'],
                    ['name' => 'third level'],
                    [
                        'name' => 'fourth level',
                        'sub' => [
                            ['name' => 'fourth sub level'],
                            [
                                'name' => 'fourth sub level 02',
                                'sub' => [
                                    ['name' => 'fourth sub sub level'],
                                    ['name' => 'fourth sub sub level 02'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'first level 02',
                'sub' => [
                    ['name' => 'second level 02'],
                    ['name' => 'third level 02'],
                    ['name' => 'fourth level 02'],
                ],
            ],
        ];

        $tree = $this->saveTree($categories, [$this->mainCategoryId]);

        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get((int) $tree[0]['sub'][2]['id'], $context);
        $context->getShop()->setCategory($category);

        $menu = $reader->get($context, 10);

        $menu = $this->extractTree($menu);

        $this->assertSame($categories[0]['sub'][2]['sub'], $menu);
    }

    public function testGetAdvancedMenuWithInactiveCategory()
    {
        $reader = Shopware()->Container()->get('shopware_storefront.advanced_menu_reader');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $categories = [
            [
                'name' => 'first level',
                'sub' => [
                    ['name' => 'second level'],
                    ['name' => 'third level'],
                    [
                        'name' => 'fourth level',
                        'active' => 0,
                        'sub' => [
                            ['name' => 'fourth sub level'],
                            [
                                'name' => 'fourth sub level 02',
                                'sub' => [
                                    ['name' => 'fourth sub sub level'],
                                    ['name' => 'fourth sub sub level 02'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'first level 02',
                'sub' => [
                    ['name' => 'second level 02'],
                    ['name' => 'third level 02'],
                    ['name' => 'fourth level 02'],
                ],
            ],
        ];

        $this->saveTree($categories, [$this->mainCategoryId]);

        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get($this->mainCategoryId, $context);

        $context->getShop()->setCategory($category);

        $menu = $reader->get($context, 10);

        $menu = $this->extractTree($menu);

        $categories = $this->prepareExpectedCategories($categories, (int) 2);

        $this->assertSame($categories, $menu);
    }

    public function testGetAdvancedMenuDepth()
    {
        $reader = Shopware()->Container()->get('shopware_storefront.advanced_menu_reader');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $categories = [
            [
                'name' => 'first level',
                'sub' => [
                    ['name' => 'second level'],
                    ['name' => 'third level'],
                    [
                        'name' => 'fourth level',
                        'active' => 0,
                        'sub' => [
                            ['name' => 'fourth sub level'],
                            [
                                'name' => 'fourth sub level 02',
                                'sub' => [
                                    ['name' => 'fourth sub sub level'],
                                    ['name' => 'fourth sub sub level 02'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'first level 02',
                'sub' => [
                    ['name' => 'second level 02'],
                    ['name' => 'third level 02'],
                    [
                        'name' => 'fourth level 02',
                        'sub' => [
                            ['name' => 'fourth sub sub level'],
                            [
                                'name' => 'fourth sub sub level 02',
                                'sub' => [
                                    [
                                        'name' => 'fifth sub sub sub level',
                                        'sub' => [
                                            ['name' => 'fifth sub sub sub level'],
                                            [
                                                'name' => 'fifth sub sub sub level 02',
                                                'active' => 0,
                                                'sub' => [
                                                    ['name' => 'fifth sub sub sub sub level'],
                                                    [
                                                        'name' => 'fifth sub sub sub sub level 02',
                                                        'sub' => [
                                                            ['name' => 'fifth sub sub sub sub sub  level'],
                                                            ['name' => 'fifth sub sub sub sub sub level 02'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'name' => 'fourth sub sub sub level 02',
                                        'sub' => [
                                            ['name' => 'fourth sub sub sub sub level'],
                                            [
                                                'name' => 'fourth sub sub sub sub level 02',
                                                'sub' => [
                                                    ['name' => 'fourth sub sub sub sub sub  level'],
                                                    ['name' => 'fourth sub sub sub sub sub level 02'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $depth = 5;

        $this->saveTree($categories, [$this->mainCategoryId]);

        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get($this->mainCategoryId, $context);

        $context->getShop()->setCategory($category);

        $menu = $reader->get($context, $depth);

        $menu = $this->extractTree($menu);

        $categories = $this->prepareExpectedCategories($categories, $depth);

        $this->assertSame($categories, $menu);
    }

    public function testGetAdvancedMenuDepthAndActive()
    {
        $reader = Shopware()->Container()->get('shopware_storefront.advanced_menu_reader');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);

        $categories = [
            [
                'name' => 'first level 02',
                'sub' => [
                    ['name' => 'second level 02'],
                    ['name' => 'third level 02'],
                    [
                        'name' => 'fourth level 02',
                        'sub' => [
                            ['name' => 'fourth sub sub level'],
                            [
                                'name' => 'fourth sub sub level 02',
                                'sub' => [
                                    ['name' => 'fourth sub sub sub level'],
                                    [
                                        'name' => 'fourth sub sub sub level 02',
                                        'sub' => [
                                            ['name' => 'fourth sub sub sub sub level'],
                                            [
                                                'name' => 'fourth sub sub sub sub level 02',
                                                'sub' => [
                                                    ['name' => 'fourth sub sub sub sub sub  level'],
                                                    ['name' => 'fourth sub sub sub sub sub level 02'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $depth = 2;

        $this->saveTree($categories, [$this->mainCategoryId]);

        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get($this->mainCategoryId, $context);

        $context->getShop()->setCategory($category);

        $menu = $reader->get($context, $depth);

        $menu = $this->extractTree($menu);

        $categories = $this->prepareExpectedCategories($categories, $depth);

        $this->assertSame($categories, $menu);
    }

    /**
     * Saves the given categories to the database and returns an array with the ids of the inserted categories.
     *
     * @param array[] $categories
     * @param array   $path
     *
     * @return array[]
     */
    private function saveTree(array $categories, array $path): array
    {
        $result = [];
        foreach ($categories as &$category) {
            $item = [];
            $category['active'] = array_key_exists('active', $category) ? $category['active'] : 1;

            $this->connection->insert('s_categories', [
                'active' => $category['active'],
                'path' => '|' . implode('|', $path) . '|',
                'description' => $category['name'],
                'parent' => $path[count($path) - 1],
            ]);

            $category['id'] = $this->connection->lastInsertId('s_categories');

            $item['name'] = $category['name'];
            $item['id'] = $category['id'];
            $item['active'] = $category['active'];

            if ($category['sub']) {
                $item['sub'] = $this->saveTree($category['sub'], array_merge($path, [$category['id']]));
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Resolves advancedMenu data structure to the simple nested tree array
     *
     * @param array[] $menu
     *
     * @return array[]
     */
    private function extractTree(array $menu): array
    {
        $result = [];
        foreach ($menu as $item) {
            $new = ['name' => $item['name']];
            if ($item['sub']) {
                $new['sub'] = $this->extractTree($item['sub']);
            }
            $result[] = $new;
        }

        return $result;
    }

    /**
     * Removes incative categorys and takes care of the category tree depth
     *
     * @param array[] $categories
     * @param int     $depth
     *
     * @return array[]
     */
    private function prepareExpectedCategories(array $categories, int $depth): array
    {
        $result = [];
        foreach ($categories as $category) {
            if ($depth == 0 || (array_key_exists('active', $category) && !$category['active'])) {
                continue;
            }
            if ($category['sub'] && $depth > 1) {
                $category['sub'] = $this->prepareExpectedCategories($category['sub'], $depth - 1);
            } else {
                unset($category['sub']);
            }
            $result[] = $category;
        }

        return $result;
    }
}
