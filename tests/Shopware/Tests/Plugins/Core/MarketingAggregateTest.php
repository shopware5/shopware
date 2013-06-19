<?php

class Shopware_Tests_Plugins_Frontend_MarketingAggregateTest extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * @return Shopware_Components_SimilarShown
     */
    protected function SimilarShown() {
        return Shopware()->SimilarShown();
    }

    /**
     * @return Shopware_Components_TopSeller
     */
    protected function TopSeller() {
        return Shopware()->TopSeller();
    }

    /**
     * @return Shopware_Components_AlsoBought
     */
    protected function AlsoBought() {
        return Shopware()->AlsoBought();
    }

    /**
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected function Db() {
        return Shopware()->Db();
    }

    /**
     * @return sArticles
     */
    protected function Articles() {
        return Shopware()->Modules()->Articles();
    }


    public function setUp() {
        parent::setUp();
    }

    protected function resetTopSeller($condition = '') {
        $this->Db()->query("DELETE FROM s_articles_top_seller_ro " . $condition);
    }

    protected function getAllTopSeller($condition = '') {
        return $this->Db()->fetchAll("SELECT * FROM s_articles_top_seller_ro " . $condition);
    }

    protected function getAllArticles($condition = '') {
        return $this->Db()->fetchAll("SELECT * FROM s_articles " . $condition);
    }


    public function testInitTopSeller()
    {
        $this->resetTopSeller();

        $this->assertArrayCount(0, $this->getAllTopSeller());

        $this->TopSeller()->initTopSeller();

        $this->TopSeller()->initTopSeller(50);

        $this->assertArrayCount(50, $this->getAllTopSeller());

        $this->TopSeller()->initTopSeller();

        $this->assertArrayCount(
            count($this->getAllArticles()),
            $this->getAllTopSeller()
        );
    }


    public function testUpdateElapsedTopSeller()
    {
        //init top seller to be sure that all articles has a row
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $this->Db()->query("UPDATE s_articles_top_seller_ro SET last_cleared = '2010-01-01'");

        //check if the update script was successfully
        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(0, $topSeller);

        //update only 50 top seller articles to test the limit function
        $this->TopSeller()->updateElapsedTopSeller(50);

        //check if only 50 top seller was updated.
        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(
            count($this->getAllTopSeller()) - 50,
            $topSeller
        );

        //now we can update the all other top seller data
        $this->TopSeller()->updateElapsedTopSeller();
        $this->assertArrayCount(
            count($this->getAllTopSeller()),
            $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ")
        );
    }


    public function testIncrementTopSeller()
    {
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $topSeller = $this->getAllTopSeller(" LIMIT 1 ");
        $topSeller = $topSeller[0];
        $initialValue = $topSeller['sales'];

        $this->TopSeller()->incrementTopSeller($topSeller['article_id'], 10);

        $topSeller = $this->getAllTopSeller(" WHERE article_id = " . $topSeller['article_id']);
        $this->assertArrayCount(1, $topSeller);
        $topSeller = $topSeller[0];

        $this->assertEquals($initialValue + 10, $topSeller['sales']);
    }

}