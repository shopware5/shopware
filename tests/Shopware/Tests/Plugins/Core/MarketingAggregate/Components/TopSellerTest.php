<?php

class Shopware_Tests_Plugins_Core_MarketingAggregate_Components_TopSellerTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

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

    public function testRefreshTopSellerForArticleId()
    {
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $topSeller = $this->getAllTopSeller(" LIMIT 1 ");
        $topSeller = $topSeller[0];

        $this->resetTopSeller();
        $this->TopSeller()->refreshTopSellerForArticleId($topSeller['article_id']);

        $allTopSeller = $this->getAllTopSeller();
        $this->assertArrayCount(1, $allTopSeller);

        $this->assertArrayEquals($topSeller, $allTopSeller[0], array('article_id', 'sales'));
    }


    public function testTopSellerLiveRefresh()
    {
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $this->saveConfig('topSellerRefreshStrategy', 3);
        Shopware()->Cache()->remove('Shopware_Config');

        $this->Db()->query("UPDATE s_articles_top_seller_ro SET last_cleared = '2010-01-01'");

        $result = $this->dispatch('/genusswelten/?p=1');
        $this->assertEquals(200, $result->getHttpResponseCode());

        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(50, $topSeller);
    }


    public function testTopSellerCronJobRefresh()
    {
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $this->saveConfig('topSellerRefreshStrategy', 2);
        Shopware()->Cache()->remove('Shopware_Config');

        $this->Db()->query("UPDATE s_articles_top_seller_ro SET last_cleared = '2010-01-01'");

        $result = $this->dispatch('/genusswelten/?p=1');
        $this->assertEquals(200, $result->getHttpResponseCode());

        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(0, $topSeller);

        $cron = $this->Db()->fetchRow("SELECT * FROM s_crontab WHERE action = 'RefreshTopSeller'");
        $this->assertNotEmpty($cron);

        //the cron plugin isn't installed, so we can't use a dispatch on /backend/cron
        $this->Plugin()->refreshTopSeller();

        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(
            count($this->getAllTopSeller()),
            $topSeller
        );
    }

    public function testTopSellerManualRefresh()
    {
        $this->resetTopSeller();
        $this->TopSeller()->initTopSeller();

        $this->saveConfig('topSellerRefreshStrategy', 1);
        Shopware()->Cache()->remove('Shopware_Config');

        $this->Db()->query("UPDATE s_articles_top_seller_ro SET last_cleared = '2010-01-01'");

        $result = $this->dispatch('/genusswelten/?p=1');
        $this->assertEquals(200, $result->getHttpResponseCode());

        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(0, $topSeller);

        //the cron plugin isn't installed, so we can't use a dispatch on /backend/cron
        $this->Plugin()->refreshTopSeller();

        $topSeller = $this->getAllTopSeller(" WHERE last_cleared > '2010-01-01' ");
        $this->assertArrayCount(0, $topSeller);
    }


}