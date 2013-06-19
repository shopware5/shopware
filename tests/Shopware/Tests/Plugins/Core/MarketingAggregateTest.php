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




    private function assertArrayEquals(array $expected, array $result, array $properties)
    {
        foreach($expected as $key => $currentExpected) {
            $currentResult = $result[$key];
            foreach($properties as $property) {
                $this->assertEquals($currentExpected[$property], $currentResult[$property]);
            }
        }
    }


    /**
     * Helper method to persist a given config value
     */
    private function saveConfig($name, $value)
    {
        $shopRepository    = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository    = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = array('name' => $name);
        if (isset($formName)) {
            $form = $formRepository->findOneBy(array('name' => $formName));
            $findBy['form'] = $form;
        }

        /** @var $element Shopware\Models\Config\Element */
        $element = $elementRepository->findOneBy($findBy);

        // If the element is empty, the given setting does not exists. This might be the case for some plugins
        // Skip those values
        if (empty($element)) {
            return;
        }

        foreach ($element->getValues() as $valueModel) {
            Shopware()->Models()->remove($valueModel);
        }

        $values = array();
        // Do not save default value
        if ($value !== $element->getValue()) {
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        Shopware()->Models()->flush($element);
    }

}