<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4724
 */
class Shopware_RegressionTests_Ticket4724 extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp()
    {
        parent::setUp();

        $sql = "UPDATE `s_blog` SET `active` = '0' WHERE `id` =3;";
        Shopware()->Db()->exec($sql, array());
    }

    /**
     * Tests the behavior if the blog article is not activated
     */
    public function testDispatchNoActiveBlogItem()
    {

        try {
            $this->dispatch('/blog/detail/?blogArticle=3');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }

        $this->assertTrue($this->Response()->isRedirect());
    }

    /**
     * Tests the behavior if the BlogItem does not exist anymore
     */
    public function testDispatchNotExistingBlogItem()
    {

        try {
            $this->dispatch('/blog/detail/?blogArticle=2222');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }

        $this->assertTrue($this->Response()->isRedirect());
    }


    /**
     * Test redirect when the blog category does not exist
     */
    public function testDispatchNotExistingBlogCategory()
    {

        try {
            $this->dispatch('/blog/?sCategory=17');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }

        $this->assertTrue(!$this->Response()->isRedirect());

        try {
            $this->dispatch('/blog/?sCategory=156165');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }

        $this->assertTrue($this->Response()->isRedirect());

        //deactivate blog category
        $sql= "UPDATE `s_categories` SET `active` = '0' WHERE `id` =17";
        Shopware()->Db()->exec($sql, array());

        //should be redirected because blog category is inactive
        try {
            $this->dispatch('/blog/?sCategory=17');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }
        $this->assertTrue($this->Response()->isRedirect());

        //should be redirected because blog category is inactive
        try {
            $this->dispatch('/blog/detail/?blogArticle=3');
        }
        catch (Exception $e) {
            $this->fail("Exception thrown. This should not occur.");
        }

        $this->assertTrue($this->Response()->isRedirect());


    }
    /**
     * Cleaning up testData
     */
    public function tearDown()
    {
        parent::tearDown();

        $sql = "UPDATE `s_blog` SET `active` = '1' WHERE `id` =3;";
        Shopware()->Db()->exec($sql, array());


        //activate blog category
        $sql= "UPDATE `s_categories` SET `active` = '1' WHERE `id` =17";
        Shopware()->Db()->exec($sql, array());

    }
}
