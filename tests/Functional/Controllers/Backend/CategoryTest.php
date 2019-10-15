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

use Shopware\Models\Category\Category;

class CategoryTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var Category
     */
    protected $repository;

    /**
     * @var array
     */
    private $dummyData = [
         'parentId' => 1,
         'name' => 'unitTestCategory',
         'active' => 1,
    ];

    private $updateMetaDescription = 'testMetaDescription';

    /** @var \Shopware\Components\Model\ModelManager */
    private $manager;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = Shopware()->Models();
        $this->repository = Shopware()->Models()->getRepository(Category::class);

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testGetList()
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
        static::assertTrue($this->View()->success);
        $returnData = $this->View()->data;

        static::assertNotEmpty($returnData);
        static::assertGreaterThan(0, $this->View()->total);
        $foundDummy = [];
        foreach ($returnData as $dummyData) {
            if ($dummyData['name'] == $dummy->getName()) {
                $foundDummy = $dummyData;
            }
        }
        static::assertTrue(!empty($foundDummy));
        $this->manager->remove($dummy);
        $this->manager->flush();
    }

    /**
     * @return int The id of the new category
     */
    public function testSaveDetail()
    {
        $params = $this->dummyData;
        unset($params['parentId']);
        $params['articles'] = [];
        $params['customerGroups'] = [];

        // Test new category
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/createDetail');
        static::assertTrue($this->View()->success);
        static::assertEquals($this->dummyData['name'], $this->View()->data['name']);

        // Test update category
        $params['id'] = $this->View()->data['id'];
        $params['metaDescription'] = $this->updateMetaDescription;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/updateDetail');
        static::assertTrue($this->View()->success);
        static::assertEquals($this->updateMetaDescription, $this->View()->data['metaDescription']);

        return $this->View()->data['id'];
    }

    /**
     * @depends testSaveDetail
     *
     * @param string $id
     *
     * @return string the id to for the testGetDetail Method
     */
    public function testGetDetail($id)
    {
        $params['node'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/getDetail');
        static::assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $dummyData = $this->dummyData;

        static::assertEquals($dummyData['parentId'], $returningData['parentId']);
        static::assertEquals($dummyData['name'], $returningData['name']);
        static::assertTrue($returningData['changed'] instanceof \DateTime);
        static::assertTrue($returningData['added'] instanceof \DateTime);

        return $id;
    }

    /**
     * Test getIdPath controller method f.e. used by product feed module
     *
     * @depends testGetDetail
     */
    public function testGetIdPath($id)
    {
        $params['categoryIds'] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/getIdPath');
        static::assertTrue($this->View()->success);
        $categoryPath = $this->View()->data;
        static::assertTrue(!empty($categoryPath));
        static::assertEquals(2, count(explode('/', $categoryPath[0])));
    }

    /**
     * Test moveTreeItem controller method
     *
     * @depends testGetDetail
     */
    public function testMoveTreeItem($id)
    {
        // Test move to another position
        $params['id'] = $id;
        $params['position'] = 2;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/moveTreeItem');
        static::assertTrue($this->View()->success);

        $params['id'] = $id;
        $params['position'] = 2;
        $params['parentId'] = 3;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/moveTreeItem');
        static::assertTrue($this->View()->success);

        $movedCategoryModel = $this->repository->find($id);
        $parentModel = $movedCategoryModel->getParent();

        // parentCategory should be Deutsch Id = 3
        static::assertEquals(3, $parentModel->getId());
    }

    /**
     * @depends testGetDetail
     *
     * @param string $id
     */
    public function testDelete($id)
    {
        $params['id'] = $id;
        $categoryModel = $this->repository->find($id);
        $categoryName = $categoryModel->getName();
        static::assertTrue(!empty($categoryName));

        $this->Request()->setParams($params);
        $this->dispatch('backend/Category/delete');
        static::assertTrue($this->View()->success);
        $categoryModel = $this->repository->find($id);
        static::assertEquals(null, $categoryModel);
    }

    /**
     * Creates the dummy data
     *
     * @return Category
     */
    private function getDummyData()
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
     *
     * @return Category
     */
    private function createDummy()
    {
        $dummyData = $this->getDummyData();
        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }
}
