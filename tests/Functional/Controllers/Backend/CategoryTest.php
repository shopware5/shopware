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

namespace Shopware\Tests\Functional\Controllers\Backend;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Enlight_Components_Test_Controller_TestCase as ControllerTestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;
use Shopware\Models\Category\Repository;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class CategoryTest extends ControllerTestCase
{
    use ContainerTrait;

    private Repository $repository;

    /**
     * @var array{parentId: int, name: string, active: bool}
     */
    private array $dummyData = [
        'parentId' => 1,
        'name' => 'unitTestCategory',
        'active' => true,
    ];

    private string $updateMetaDescription = 'testMetaDescription';

    private ModelManager $manager;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get('models');
        $this->repository = $this->manager->getRepository(Category::class);

        // Disable auth and acl
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAuth();
        $this->getContainer()->get('plugins')->Backend()->Auth()->setNoAcl();
    }

    public function testGetList(): void
    {
        // Delete old data
        $repositoryData = $this->repository->findBy(['name' => $this->dummyData['name']]);
        foreach ($repositoryData as $testDummy) {
            $this->manager->remove($testDummy);
        }
        $this->manager->flush();

        $dummy = $this->createDummy();

        $params['node'] = 1;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/getList');
        static::assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');

        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->getAssign('total'));
        $foundDummy = [];
        foreach ($returnData as $dummyData) {
            if ($dummyData['name'] === $dummy->getName()) {
                $foundDummy = $dummyData;
            }
        }
        static::assertNotEmpty($foundDummy);
        $this->manager->remove($dummy);
        $this->manager->flush();
    }

    /**
     * @return int The id of the new category
     */
    public function testSaveDetail(): int
    {
        $params = $this->dummyData;
        unset($params['parentId']);
        $params['articles'] = [];
        $params['customerGroups'] = [];

        // Test new category
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/createDetail');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($this->dummyData['name'], $this->View()->getAssign('data')['name']);

        // Test update category
        $params['id'] = $this->View()->getAssign('data')['id'];
        $params['metaDescription'] = $this->updateMetaDescription;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/updateDetail');
        static::assertTrue($this->View()->getAssign('success'));
        static::assertEquals($this->updateMetaDescription, $this->View()->getAssign('data')['metaDescription']);

        return $this->View()->getAssign('data')['id'];
    }

    /**
     * @throws Exception
     */
    public function testSaveDetailFullParam(): void
    {
        $this->Request()->setParams([
            'facetIds' => '|12|5|2|4|10|11|3|6|7|8|9|',
            'id' => 5,
        ]);
        $this->dispatch('backend/Category/updateDetail');

        $result = $this->getContainer()->get(Connection::class)->executeQuery(
            "SELECT facet_ids FROM s_categories WHERE description = 'Genusswelten'"
        );
        $result = $result->fetchAllAssociative();

        static::assertSame('|12|5|2|4|10|11|3|6|7|8|9|', $result[0]['facet_ids']);
    }

    /**
     * @depends testSaveDetail
     *
     * @return int the id to for the testGetDetail Method
     */
    public function testGetDetail(int $id): int
    {
        $params['node'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/getDetail');
        static::assertTrue($this->View()->getAssign('success'));
        $returningData = $this->View()->getAssign('data');
        $dummyData = $this->dummyData;

        static::assertEquals($dummyData['parentId'], $returningData['parentId']);
        static::assertEquals($dummyData['name'], $returningData['name']);
        static::assertInstanceOf(DateTime::class, $returningData['changed']);
        static::assertInstanceOf(DateTime::class, $returningData['added']);

        return $id;
    }

    /**
     * Test getIdPath controller method f.e. used by product feed module
     *
     * @depends testGetDetail
     */
    public function testGetIdPath(int $id): void
    {
        $params['categoryIds'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/getIdPath');
        static::assertTrue($this->View()->getAssign('success'));
        $categoryPath = $this->View()->getAssign('data');
        static::assertNotEmpty($categoryPath);
        static::assertCount(2, explode('/', $categoryPath[0]));
    }

    /**
     * Test moveTreeItem controller method
     *
     * @depends testGetDetail
     */
    public function testMoveTreeItem(int $id): void
    {
        // Test move to another position
        $params['id'] = $id;
        $params['position'] = 2;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/moveTreeItem');
        static::assertTrue($this->View()->getAssign('success'));

        $params['id'] = $id;
        $params['position'] = 2;
        $params['parentId'] = 3;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/moveTreeItem');
        static::assertTrue($this->View()->getAssign('success'));

        $movedCategoryModel = $this->repository->find($id);
        static::assertNotNull($movedCategoryModel);
        $parentModel = $movedCategoryModel->getParent();
        static::assertNotNull($parentModel);

        // parentCategory should be "Deutsch" ID = 3
        static::assertEquals(3, $parentModel->getId());
    }

    /**
     * @depends testGetDetail
     */
    public function testDelete(int $id): void
    {
        $params['id'] = $id;
        $categoryModel = $this->repository->find($id);
        static::assertNotNull($categoryModel);
        $categoryName = $categoryModel->getName();
        static::assertNotEmpty($categoryName);

        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/delete');
        static::assertTrue($this->View()->getAssign('success'));
        $categoryModel = $this->repository->find($id);
        static::assertNull($categoryModel);
    }

    public function testUpdatingInvalidCategory(): void
    {
        $params = $this->dummyData;
        unset($params['parentId']);
        $params['id'] = -10000;
        $params['articles'] = [];
        $params['customerGroups'] = [];

        // Test new category
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/createDetail');
        static::assertFalse($this->View()->getAssign('success'));
        static::assertNotEmpty($this->View()->getAssign('message'));

        $snippet = $this->getContainer()->get('snippets')->getNamespace('backend/category/main');
        static::assertEquals($snippet->get('saveDetailInvalidCategoryId', 'Invalid categoryId'), $this->View()->getAssign('message'));
    }

    /**
     * Creates the dummy data
     */
    private function getDummyData(): Category
    {
        $dummyModel = new Category();
        $dummyData = $this->dummyData;

        $dummyModel->fromArray($dummyData);
        // Set category parent
        $parent = $this->repository->find($dummyData['parentId']);
        $dummyModel->setParent($parent);

        return $dummyModel;
    }

    /**
     * Helper method to create the dummy object
     */
    private function createDummy(): Category
    {
        $dummyData = $this->getDummyData();
        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }
}
