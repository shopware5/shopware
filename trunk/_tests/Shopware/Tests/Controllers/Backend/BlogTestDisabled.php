<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     M.Schmaeing
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Blog
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_BlogTest extends Enlight_Components_Test_Controller_TestCase
{


    /**
     * dummy data
     *
     * @var array
     */
    private $dummyData = array(
         'title' => 'phpUnitTestTitle',
         'active' => 1,
         'shortDescription' => 'phpUnitTestShortDescription',
         'description' => 'phpUnitTestDescription',
         'views' => 1337,
         'template' => '1337',
         'displayDate' => '2012-08-01 00:30:00',
         'categoryId' => 1151
    );

    /**
     * dummy data
     *
     * @var array
     */
    private $dummyCommentData = array(
         'name' => 'phpUnitTestComment',
         'headline' => 'phpUnitTestHeadline',
         'comment' => 'phpUnitTestComment',
         'points' => 5,
         'active' => 0,
         'email' => 'test@example.com',
         'creationDate' => '2012-08-01 00:30:00',
    );

    /**
     * default blog category id
     * @var int
     */
    private $blogCategoryId = 1151;

    private $updateShortDescription = "testUpdatedShortDescription";

    /** @var Shopware\Components\Model\ModelManager */
    private $manager = null;

    /**@var $model \Shopware\Models\Blog\Blog*/
    protected $repository = null;

    /**@var $model \Shopware\Models\Blog\Comment*/
    protected $commentRepository = null;


    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        $this->manager    = Shopware()->Models();
        $this->repository = Shopware()->Models()->Blog();
        $this->commentRepository = Shopware()->Models()->getRepository('Shopware\Models\Blog\Comment');

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Creates the dummy data
     *
     * @return \Shopware\Models\Blog\Blog
     */
    private function getDummyData()
    {
        $dummyModel = new \Shopware\Models\Blog\Blog();
        $dummyData = $this->dummyData;

        $dummyModel->fromArray($dummyData);
        return $dummyModel;
    }

    /**
     * Creates the dummy comment data
     *
     * @param $blogId
     * @return \Shopware\Models\Blog\Comment
     */
    private function getDummyCommentData($blogId)
    {
        $dummyModel = new \Shopware\Models\Blog\Comment();
        $dummyData = $this->dummyCommentData;
        /** @var $blogModel \Shopware\Models\Blog\Blog */
        $blogModel = $this->repository->find($blogId);
        $dummyData["blog"] = $blogModel;
        $dummyModel->fromArray($dummyData);
        return $dummyModel;
    }

    /**
     * helper method to create the dummy object
     *
     * @return \Shopware\Models\Blog\Blog
     */
    private function createDummy()
    {
        $dummyData = $this->getDummyData();
        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }

    /**
     * helper method to create the dummy object
     *
     * @param $blogId
     * @return \Shopware\Models\Blog\Blog
     */
    private function createDummyComments($blogId)
    {
        $dummyData = $this->getDummyCommentData($blogId);
        $this->manager->persist($dummyData);
        $this->manager->flush();

        return $dummyData;
    }

    /**
     * test getList controller action
     */
    public function testGetList()
    {
        $this->deleteOldData();

        $dummy = $this->createDummy();


        /** @var Enlight_Controller_Response_ResponseTestCase */
        $params["categoryId"] = 1151;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/getList');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;

        $this->assertNotEmpty($returnData);
        $foundDummy = array();
        foreach ($returnData as $dummyData) {
            if($dummyData["title"] == $dummy->getTitle()){
                $foundDummy = $dummyData;
            }
        }
        $this->assertTrue(!empty($foundDummy));
        $this->manager->remove($dummy);
        $this->manager->flush();


    }

    /**
     * test testGetBlogCategories controller action
     */
    public function testGetBlogCategories()
    {
        $this->dispatch('backend/Blog/getBlogCategories');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;

        $this->assertNotEmpty($returnData);

        $foundDummy = array();
        foreach ($returnData as $dummyData) {
            if($dummyData["id"] == $this->blogCategoryId){
                $foundDummy = $dummyData;
            }
        }
        $this->assertTrue(!empty($foundDummy));
    }


    /**
     * test saveDetail controller action
     *
     * @return the id of the new blog article
     */
    public function testSaveDetail()
    {
        $params = $this->dummyData;

        $params["assignedArticles"] = array();
        $params["media"] = array();

        //test new blog
        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/saveBlogArticle');
        $this->assertTrue($this->View()->success);
        $this->assertArrayCount(1, $this->View()->data);
        $this->assertEquals($this->dummyData["title"], $this->View()->data["title"]);


        //test update blog
        $params["id"] = $this->View()->data["id"];
        $params["shortDescription"] = $this->updateShortDescription;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/saveBlogArticle');
        $this->assertTrue($this->View()->success);
        $this->assertEquals($this->updateShortDescription, $this->View()->data["shortDescription"]);

        //create blog dummy comments
        $this->createDummyComments($params["id"]);

        return $this->View()->data["id"];
    }

    /**
     * test getDetail controller action
     *
     * @depends testSaveDetail
     * @param $id
     * @return the id to for the testGetDetail Method
     */
    public function testGetDetail($id)
    {
        $filter = array(array('property' => 'id', 'value' => $id));
        $params["filter"] = Zend_Json::encode($filter);
        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/getDetail');
        $this->assertTrue($this->View()->success);
        $returningData = $this->View()->data;
        $dummyData = $this->dummyData;

        $this->assertEquals($dummyData["title"],$returningData["title"]);
        $this->assertEquals($this->updateShortDescription,$returningData["shortDescription"]);
        $this->assertEquals($dummyData["description"],$returningData["description"]);

        return $id;
    }

    /**
     * test getList controller action
     * @depends testSaveDetail
     */
    public function testGetBlogComments($id)
    {
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $params["blogId"] = $id;
        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/getBlogComments');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;


        $this->assertNotEmpty($returnData);

        $foundDummy = array();
        $dummy = $this->getDummyCommentData($id);
        foreach ($returnData as $dummyData) {
            if($dummyData["name"] == $dummy->getName()){
                $foundDummy = $dummyData;
            }
        }
        $this->assertTrue(!empty($foundDummy));
    }

    /**
     * test getTemplates controller action
     */
    public function testGetTemplates()
    {
        $this->dispatch('backend/Blog/getTemplates');
        $this->assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        $this->assertNotEmpty($returnData);
    }

    /**
     * test delete blog comment controller action
     */
    public function testAcceptBlogComment()
    {
        $repositoryData = $this->commentRepository->findBy(array('name' => $this->dummyCommentData["name"]));
        foreach($repositoryData as $testDummy) {
            /** @var $blogModel \Shopware\Models\Blog\Comment */
            $params["id"] = $testDummy->getId();
            $this->Request()->setParams($params);
            $this->dispatch('backend/Blog/acceptBlogComment');
            $this->assertTrue($this->View()->success);
            $blogModel = $this->commentRepository->find($testDummy->getId());
            $this->assertTrue($blogModel->getActive());
        }
    }

    /**
     * test delete blog comment controller action
     *
     */
    public function testDeleteBlogComment()
    {
        $repositoryData = $this->commentRepository->findBy(array('name' => $this->dummyCommentData["name"]));
        foreach($repositoryData as $testDummy) {
            /** @var $blogModel \Shopware\Models\Blog\Comment */
            $params["id"] = $testDummy->getId();
            $this->Request()->setParams($params);
            $this->dispatch('backend/Blog/deleteBlogComment');
            $this->assertTrue($this->View()->success);
            $blogModel = $this->commentRepository->find($params["id"]);
            $this->assertEquals(null, $blogModel);
        }
    }


    /**
     * test delete controller action
     *
     * @depends testSaveDetail
     * @param $id
     */
    public function testDeleteBlogArticle($id)
    {
        $params["id"] = $id;
        /** @var $blogModel \Shopware\Models\Blog\Blog */
        $blogModel = $this->repository->find($id);
        $blogTitle = $blogModel->getTitle();
        $this->assertTrue(!empty($blogTitle));

        $this->Request()->setParams($params);
        $this->dispatch('backend/Blog/deleteBlogArticle');
        $this->assertTrue($this->View()->success);
        $blogModel = $this->repository->find($id);
        $this->assertEquals(null, $blogModel);
    }

    /**
     * Deletes the old data
     */
    private function deleteOldData()
    {
        //delete old data
        $repositoryData = $this->repository->findBy(array('title' => $this->dummyData["title"]));
        foreach ($repositoryData as $testDummy) {
            $this->manager->remove($testDummy);
        }
        $this->manager->flush();

        //delete old data
        $repositoryData = $this->commentRepository->findBy(array('name' => $this->dummyCommentData["name"]));
        foreach ($repositoryData as $testDummy) {
            $this->manager->remove($testDummy);
        }
        $this->manager->flush();
    }
}
