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


}