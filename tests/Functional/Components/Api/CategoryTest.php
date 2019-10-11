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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Category;
use Shopware\Components\Api\Resource\Resource;

class CategoryTest extends TestCase
{
    /**
     * @var Category
     */
    protected $resource;

    /**
     * @return Category
     */
    public function createResource()
    {
        return new Category();
    }

    public function testCreateShouldBeSuccessful()
    {
        $date = new \DateTime();
        $date->modify('-10 days');
        $added = $date->format(\DateTime::ISO8601);

        $date->modify('-3 day');
        $changed = $date->format(\DateTime::ISO8601);

        $testData = [
            'name' => 'fooobar',
            'parent' => 1,

            'position' => 3,

            'metaKeywords' => 'test, test',
            'metaDescription' => 'Description Test',
            'cmsHeadline' => 'cms headline',
            'cmsText' => 'cmsTest',

            'active' => true,
            'blog' => false,

            'external' => false,
            'hidefilter' => false,
            'hideTop' => true,

            'changed' => $changed,
            'added' => $added,

            'attribute' => [
                1 => 'test1',
                2 => 'test2',
                6 => 'test6',
            ],
        ];

        /** @var \Shopware\Models\Category\Category $category */
        $category = $this->resource->create($testData);

        static::assertInstanceOf(\Shopware\Models\Category\Category::class, $category);
        static::assertGreaterThan(0, $category->getId());

        static::assertEquals($category->getActive(), $testData['active']);
        static::assertEquals($category->getMetaDescription(), $testData['metaDescription']);
        static::assertEquals($category->getAttribute()->getAttribute1(), $testData['attribute'][1]);
        static::assertEquals($category->getAttribute()->getAttribute2(), $testData['attribute'][2]);
        static::assertEquals($category->getAttribute()->getAttribute6(), $testData['attribute'][6]);

        return $category->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $category = $this->resource->getOne($id);
        static::assertGreaterThan(0, $category['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = [
            'active' => true,
            'name' => uniqid(rand()) . 'testkategorie',
            'attribute' => [1 => 'nase'],
        ];

        $category = $this->resource->update($id, $testData);

        static::assertInstanceOf(\Shopware\Models\Category\Category::class, $category);
        static::assertEquals($id, $category->getId());

        static::assertEquals($category->getActive(), $testData['active']);
        static::assertEquals($category->getName(), $testData['name']);
        static::assertEquals($category->getAttribute()->getAttribute1(), $testData['attribute'][1]);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $category = $this->resource->delete($id);

        static::assertInstanceOf('\Shopware\Models\Category\Category', $category);
        static::assertEquals(null, $category->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete('');
    }

    public function testfindCategoryByPath()
    {
        $parts = [
            'Deutsch',
            'Foo' . uniqid(rand()),
            'Bar' . uniqid(rand()),
        ];

        $path = implode('|', $parts);

        /** @var \Shopware\Models\Category\Category $category */
        $category = $this->resource->findCategoryByPath($path);
        static::assertEquals(null, $category);

        $category = $this->resource->findCategoryByPath($path, true);
        $this->resource->flush();

        static::assertEquals(array_pop($parts), $category->getName());
        static::assertEquals(array_pop($parts), $category->getParent()->getName());
        static::assertEquals(array_pop($parts), $category->getParent()->getParent()->getName());
        static::assertEquals(3, $category->getParent()->getParent()->getId());

        $secondCategory = $this->resource->findCategoryByPath($path, true);
        $this->resource->flush();

        static::assertSame($category->getId(), $secondCategory->getId());
    }

    public function testCreateCategoryWithTranslation()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface $crud */
        $crud = Shopware()->Container()->get('shopware_attribute.crud_service');

        $crud->update('s_categories_attributes', 'underscore_test', 'string');

        $categoryData = [
            'name' => 'German',
            'parent' => 3,
            'translations' => [
                2 => [
                    'shopId' => 2,
                    'description' => 'Englisch',
                    '__attribute_attribute1' => 'Attr1',
                    '__attribute_underscore_test' => 'Attribute with underscore',
                ],
            ],
        ];
        $category = $this->resource->create($categoryData);
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);

        $categoryResult = $this->resource->getOne($category->getId());

        if (isset($categoryData['translations'])) {
            static::assertEquals($categoryData['translations'], $categoryResult['translations']);
        }

        static::assertEquals(1, Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_core_translations WHERE objecttype = "category" AND objectkey = ?', [
            $category->getId(),
        ]));

        $this->resource->delete($category->getId());

        static::assertEquals(0, Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_core_translations WHERE objecttype = "category" AND objectkey = ?', [
            $category->getId(),
        ]));

        $crud->delete('s_categories_attributes', 'underscore_test');
    }

    public function testCreateCategoryWithTranslationWithUpdate()
    {
        $categoryData = [
            'name' => 'German',
            'parent' => 3,
            'translations' => [
                2 => [
                    'shopId' => 2,
                    'description' => 'Englisch',
                    '__attribute_attribute1' => 'Attr1',
                ],
            ],
        ];
        $category = $this->resource->create($categoryData);
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);

        $categoryResult = $this->resource->getOne($category->getId());

        if (isset($categoryData['translations'])) {
            static::assertEquals($categoryData['translations'], $categoryResult['translations']);
        }

        static::assertEquals(1, Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_core_translations WHERE objecttype = "category" AND objectkey = ?', [
            $category->getId(),
        ]));

        $categoryData = [
            'name' => 'German',
            'parent' => 3,
            'translations' => [
                2 => [
                    'shopId' => 2,
                    'description' => 'Englisch2',
                    '__attribute_attribute1' => 'Attr13',
                ],
            ],
        ];
        $category = $this->resource->update($category->getId(), $categoryData);

        $categoryResult = $this->resource->getOne($category->getId());

        if (isset($categoryData['translations'])) {
            static::assertEquals($categoryData['translations'], $categoryResult['translations']);
        }

        $this->resource->delete($category->getId());

        static::assertEquals(0, Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_core_translations WHERE objecttype = "category" AND objectkey = ?', [
            $category->getId(),
        ]));
    }
}
