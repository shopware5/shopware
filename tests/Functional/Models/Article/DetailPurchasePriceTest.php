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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2014, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Models_Article_DetailPurchasePriceTest extends Enlight_Components_Test_TestCase
{
    public function testSetPurchasePrice()
    {
        $articleDetail = $this->getTestArticleDetail();
        $articleDetailId = $articleDetail->getId();

        $newPurchasePrice = 123.45;
        $articleDetail->setPurchasePrice($newPurchasePrice);

        Shopware()->Models()->flush();

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);
    }

    public function testSetPurchasePriceWithSingleEntityFlush()
    {
        $articleDetail = $this->getTestArticleDetail();
        $articleDetailId = $articleDetail->getId();

        $newPurchasePrice = 234.56;
        $articleDetail->setPurchasePrice($newPurchasePrice);

        Shopware()->Models()->flush($articleDetail);

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);
    }

    public function testPriceSetBasePrice()
    {
        $articleDetail = $this->getTestArticleDetail();
        $articleDetailId = $articleDetail->getId();

        $newPurchasePrice = 345.67;
        $price = $articleDetail->getPrices()->first();
        $price->setBasePrice($newPurchasePrice);

        Shopware()->Models()->flush();

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);
    }

    public function testPriceSetBasePriceWithSingleEntityFlush()
    {
        $articleDetail = $this->getTestArticleDetail();
        $articleDetailId = $articleDetail->getId();

        $newPurchasePrice = 456.78;
        $price = $articleDetail->getPrices()->first();
        $price->setBasePrice($newPurchasePrice);

        Shopware()->Models()->flush($price);

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);
    }

    public function testPriceSetDetail()
    {
        $articleDetail = $this->getTestArticleDetail();
        $articleDetailId = $articleDetail->getId();

        $newPurchasePrice = 567.89;
        $articleDetail->setPurchasePrice($newPurchasePrice);

        Shopware()->Models()->flush();

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);

        $articleDetail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $articleDetailId);
        $price = new \Shopware\Models\Article\Price();
        $this->assertEquals(0, $price->getBasePrice());

        $price->setDetail($articleDetail);
        $this->assertEquals($newPurchasePrice, $price->getBasePrice());

        $price->setArticle($articleDetail->getArticle());
        $articleDetail->getPrices()->add($price);
        Shopware()->Models()->persist($price);
        Shopware()->Models()->flush();

        $this->assertPurchasePriceAndBasePrices($articleDetailId, $newPurchasePrice);
    }

    private function getTestArticleDetail()
    {
        $ids = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')
            ->createQueryBuilder('d')
            ->select('d.id')
            ->getQuery()
            ->getArrayResult();

        shuffle($ids);

        return Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')->find(array_shift($ids));
    }

    private function assertPurchasePriceAndBasePrices($articleDetailId, $expectedPrice)
    {
        Shopware()->Models()->clear();
        $articleDetail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $articleDetailId);
        $this->assertEquals($expectedPrice, $articleDetail->getPurchasePrice());
        foreach ($articleDetail->getPrices() as $price) {
            $this->assertEquals($expectedPrice, $price->getBasePrice());
        }
    }
}
