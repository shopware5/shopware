<?php
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

namespace Shopware\Tests\Functional\Modules\Category;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_TestCase;
use sCategories;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class sGetCategoriesTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var sCategories
     */
    private $module;

    protected function setUp(): void
    {
        $this->helper = new Helper($this->getContainer());
        $connection = $this->getContainer()->get(Connection::class);

        $connection->executeQuery(
            "DELETE FROM s_categories WHERE description LIKE 'Foo%' AND parent = 3"
        );

        $this->module = new sCategories();
        $this->module->baseId = 3;
        $this->module->customerGroupId = 1;

        parent::setUp();
    }

    public function testWithMainId()
    {
        $first1 = $this->helper->createCategory(['name' => 'first1', 'parent' => 3]);
        $first2 = $this->helper->createCategory(['name' => 'first2', 'parent' => 3]);
        $second1 = $this->helper->createCategory(['name' => 'second1', 'parent' => $first1->getId()]);
        $this->helper->createCategory(['name' => 'third1', 'parent' => $second1->getId()]);
        $this->helper->createCategory(['name' => 'third2', 'parent' => $second1->getId()]);

        $result = $this->module->sGetCategories(3);

        static::assertCount(8, $result);
        static::assertArrayHasKey($first1->getId(), $result);
        static::assertArrayHasKey($first2->getId(), $result);

        foreach ($result as $category) {
            static::assertCount(0, $category['subcategories']);
        }
    }

    public function testWithSecondLevel()
    {
        $first1 = $this->helper->createCategory(['name' => 'first1', 'parent' => 3]);
        $first2 = $this->helper->createCategory(['name' => 'first2', 'parent' => 3]);
        $second1 = $this->helper->createCategory(['name' => 'second1', 'parent' => $first1->getId()]);
        $second2 = $this->helper->createCategory(['name' => 'second2', 'parent' => $first1->getId()]);
        $this->helper->createCategory(['name' => 'third1', 'parent' => $second1->getId()]);
        $this->helper->createCategory(['name' => 'third2', 'parent' => $second2->getId()]);

        $result = $this->module->sGetCategories($first1->getId());

        static::assertCount(8, $result);
        static::assertArrayHasKey($first1->getId(), $result);
        static::assertArrayHasKey($first2->getId(), $result);

        foreach ($result as $category) {
            if ($category['id'] != $first1->getId()) {
                static::assertCount(0, $category['subcategories']);
            }
        }

        $level1 = $this->assertAndGetSubCategories(
            $result[$first1->getId()],
            [$second1->getId(), $second2->getId()]
        );

        $this->assertAndGetSubCategories(
            $level1[$second1->getId()],
            []
        );

        $this->assertAndGetSubCategories(
            $level1[$second2->getId()],
            []
        );
    }

    public function testWithThirdLevel()
    {
        $first1 = $this->helper->createCategory(['name' => 'first1', 'parent' => 3]);
        $first2 = $this->helper->createCategory(['name' => 'first2', 'parent' => 3]);
        $second1 = $this->helper->createCategory(['name' => 'second1', 'parent' => $first1->getId()]);
        $second2 = $this->helper->createCategory(['name' => 'second2', 'parent' => $first1->getId()]);
        $third1 = $this->helper->createCategory(['name' => 'third1', 'parent' => $second1->getId()]);
        $this->helper->createCategory(['name' => 'third2', 'parent' => $second2->getId()]);

        $result = $this->module->sGetCategories($second1->getId());

        static::assertCount(8, $result);
        static::assertArrayHasKey($first1->getId(), $result);
        static::assertArrayHasKey($first2->getId(), $result);

        foreach ($result as $category) {
            if ($category['id'] != $first1->getId()) {
                static::assertCount(0, $category['subcategories']);
            }
        }

        $level1 = $this->assertAndGetSubCategories(
            $result[$first1->getId()],
            [$second1->getId(), $second2->getId()]
        );

        $this->assertAndGetSubCategories(
            $level1[$second1->getId()],
            [$third1->getId()]
        );

        $this->assertAndGetSubCategories(
            $level1[$second2->getId()],
            []
        );
    }

    public function testWithFourthLevel()
    {
        $first1 = $this->helper->createCategory(['name' => 'first1',   'parent' => 3]);
        $first2 = $this->helper->createCategory(['name' => 'first2',   'parent' => 3]);
        $second1 = $this->helper->createCategory(['name' => 'second1',  'parent' => $first1->getId()]);
        $second2 = $this->helper->createCategory(['name' => 'second2',  'parent' => $first1->getId()]);
        $third1 = $this->helper->createCategory(['name' => 'third1',   'parent' => $second1->getId()]);
        $fourth1 = $this->helper->createCategory(['name' => 'fourth1', 'parent' => $third1->getId()]);

        $result = $this->module->sGetCategories($third1->getId());

        Shopware()->Db()->delete('s_categories', 'description = "ListingTest"');

        static::assertCount(8, $result);
        static::assertArrayHasKey($first1->getId(), $result);
        static::assertArrayHasKey($first2->getId(), $result);

        foreach ($result as $category) {
            if ($category['id'] != $first1->getId()) {
                static::assertCount(0, $category['subcategories']);
            }
        }

        $level1 = $this->assertAndGetSubCategories(
            $result[$first1->getId()],
            [$second1->getId(), $second2->getId()]
        );

        $level2 = $this->assertAndGetSubCategories(
            $level1[$second1->getId()],
            [$third1->getId()]
        );

        $this->assertAndGetSubCategories(
            $level2[$third1->getId()],
            [$fourth1->getId()]
        );
        $this->assertAndGetSubCategories(
            $level1[$second2->getId()],
            []
        );

        static::assertTrue($result[$first1->getId()]['flag']);
        static::assertTrue($level1[$second1->getId()]['flag']);
        static::assertTrue($level2[$third1->getId()]['flag']);
    }

    public function testCategoryData()
    {
        $first1 = $this->helper->createCategory(['name' => 'first1', 'parent' => 3]);
        $second1 = $this->helper->createCategory(['name' => 'second1',  'parent' => $first1->getId()]);

        Shopware()->Db()->executeUpdate('UPDATE s_categories SET mediaID = 564 WHERE id = ?', [$first1->getId()]);

        $result = $this->module->sGetCategories($second1->getId());
        $mediaService = $this->getContainer()->get(MediaServiceInterface::class);

        $result = array_values($result);
        $category = $result[1];

        $keys = ['id', 'name', 'metaKeywords', 'metaDescription', 'cmsHeadline', 'cmsText',
            'active', 'template', 'blog', 'path', 'external', 'hideFilter', 'hideTop',
            'media', 'attribute', 'description', 'childrenCount', 'hidetop',
            'subcategories', 'link', 'flag',
        ];

        foreach ($keys as $key) {
            static::assertArrayHasKey($key, $category);
        }

        $expected = [
            0 => [
                'name' => 'first1',
                'media' => [
                    'id' => 564,
                    'name' => 'deli_teaser503886c2336e3',
                    'description' => '',
                    'path' => $mediaService->getUrl('media/image/deli_teaser503886c2336e3.jpg'),
                    'type' => 'IMAGE',
                    'extension' => 'jpg',
                ],
            ],
            1 => [
                'name' => 'Genusswelten',
                'template' => null,
                'childrenCount' => 3,
            ],
            2 => [
                'name' => 'Freizeitwelten',
                'path' => '|3|',
                'childrenCount' => 2,
            ],
            3 => [
                'name' => 'Wohnwelten',
                'attribute' => [
                    'id' => 6,
                    'categoryID' => 8,
                    'attribute1' => '',
                    'attribute2' => '',
                    'attribute3' => '',
                    'attribute4' => '',
                    'attribute5' => '',
                    'attribute6' => '',
                ],
                'link' => 'shopware.php?sViewport=cat&sCategory=8',
                'childrenCount' => 3,
            ],
            4 => [
                'name' => 'Sommerwelten',
                'link' => 'shopware.php?sViewport=cat&sCategory=6',
                'childrenCount' => 4,
            ],
            5 => [
                'name' => 'Beispiele',
                'childrenCount' => 10,
            ],
            6 => ['name' => 'Trends + News'],
        ];

        foreach ($expected as $index => $expectedCategory) {
            $category = $result[$index];

            foreach ($expectedCategory as $property => $value) {
                if (\is_array($value)) {
                    $array = $category[$property];

                    foreach ($value as $arrayProperty => $arrayValue) {
                        static::assertEquals(
                            $arrayValue,
                            $array[$arrayProperty],
                            'Property ' . $property . ' - ' . $arrayProperty . ' not match '
                        );
                    }
                } else {
                    static::assertEquals($value, $category[$property], 'Property ' . $property . ' not match ');
                }
            }
        }
    }

    public function testBlockedCustomerGroups()
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId()]);
        $third = $this->helper->createCategory(['name' => 'third',   'parent' => $second->getId()]);
        Shopware()->Db()->query(
            'INSERT INTO s_categories_avoid_customergroups (categoryID, customerGroupID) VALUES (?, ?)',
            [$third->getId(), 1]
        );

        $result = $this->module->sGetCategories($second->getId());

        static::assertArrayHasKey($first->getId(), $result);

        $level1 = $this->assertAndGetSubCategories(
            $result[$first->getId()],
            [$second->getId()]
        );

        $second = $level1[$second->getId()];
        static::assertEmpty($second['subcategories']);
    }

    public function testOnlyActiveCategories()
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId()]);
        $this->helper->createCategory(['name' => 'third',  'parent' => $second->getId(), 'active' => false]);

        $result = $this->module->sGetCategories($second->getId());

        static::assertArrayHasKey($first->getId(), $result);

        $level1 = $this->assertAndGetSubCategories(
            $result[$first->getId()],
            [$second->getId()]
        );

        $second = $level1[$second->getId()];
        static::assertEmpty($second['subcategories']);
    }

    public function testPositionSorting()
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId(), 'position' => 1]);
        $third = $this->helper->createCategory(['name' => 'third',  'parent' => $first->getId(), 'position' => 2]);
        $fourth = $this->helper->createCategory(['name' => 'fourth', 'parent' => $first->getId(), 'position' => 2]);

        $result = $this->module->sGetCategories($second->getId());

        static::assertArrayHasKey($first->getId(), $result);

        $level1 = $this->assertAndGetSubCategories(
            $result[$first->getId()],
            [$second->getId(), $third->getId(), $fourth->getId()]
        );

        $level1 = array_values($level1);

        static::assertEquals($level1[0]['id'], $second->getId());
        static::assertEquals($level1[1]['id'], $third->getId());
        static::assertEquals($level1[2]['id'], $fourth->getId());
    }

    private function assertAndGetSubCategories($category, $expectedIds)
    {
        $sub = $category['subcategories'];
        static::assertCount(\count($expectedIds), $sub);

        foreach ($expectedIds as $id) {
            static::assertArrayHasKey($id, $sub);
        }

        return $sub;
    }
}
