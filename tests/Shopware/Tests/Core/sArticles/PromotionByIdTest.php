<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * Class sArticles_PromotionByIdTest
 */
class sArticles_PromotionByIdTest extends Shopware_Tests_Service_Base
{
    public function testSimplePromotionById()
    {
        $this->removeArticle('Test-1');
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail('Test-1');
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $this->getApi()->create($data);

        $this->switchState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $promotion = $this->getPromotion('Test-1');

        $this->assertNotEmpty($promotion);
        $this->assertEquals($data['name'], $promotion['articleName']);

        $this->assertEquals('Test-1', $promotion['ordernumber']);

        $this->assertEquals('119,00', $promotion['price']);

        $this->assertEquals(19.00, $promotion['tax']);

        $this->assertEquals('TE', $promotion['pricegroup']);

        $this->removeCustomerGroup('TE');
        $this->removeArticle('Test-1');
    }

    public function testArticleCover()
    {
        $this->removeArticle('Test-2');
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail('Test-2');
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $data['images'] = $this->getMedia(5);
        $cover = $this->getSpecifyMedia(2);
        $data['images'][] = array(
            'main' => 1,
            'mediaId' => $cover->getId()
        );

        $this->getApi()->create($data);

        $this->switchState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $promotion = $this->getPromotion('Test-2');

        $image = $promotion['image'];

        $this->assertStringEndsWith(
            $cover->getName() . '.' . $cover->getExtension(),
            $image['src']['original']
        );

        $this->assertCount(7, $image['src']);

        $this->assertEquals('jpg', $image['extension']);

        $this->removeArticle('Test-2');
    }

    public function testProperties()
    {
        $number = 'Properties-1';
        $this->removeArticle($number);
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $data = array_merge(
            $data,
            $this->createProperties()
        );

        $this->getApi()->create($data);

        $this->switchState($group, $this->getShop(), $this->getHighTax());

        $promotion = $this->getPromotion($number);

        $this->assertArrayHasKey('sProperties', $promotion);

        $properties = $promotion['sProperties'];
        $this->assertCount(2, $properties);

        $properties = array_values($properties);

        $this->assertEquals('TEST-SET', $properties[0]['groupName']);
        $this->assertEquals('TEST-GROUP-1', $properties[0]['name']);
        $this->assertCount(5, $properties[0]['values']);


        $this->assertEquals('TEST-SET', $properties[1]['groupName']);
        $this->assertEquals('TEST-GROUP-2', $properties[1]['name']);
        $this->assertCount(5, $properties[1]['values']);

        $this->removeArticle($number);
    }

    public function testVoteAverage()
    {
        $number = 'Votes-1';
        $this->removeArticle($number);

        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $article = $this->getApi()->create($data);

        $this->createArticleVotes($article->getId());

        $this->switchState($group, $this->getShop(), $this->getHighTax());

        $promotion = $this->getPromotion($number);

        $average = $promotion['sVoteAverange'];

        $this->assertEquals('4', $average['averange']);
        $this->assertEquals('3', $average['count']);

        $this->removeArticle($number);
    }

    public function testTranslation()
    {
        $number = 'Translation-1';
        $this->removeArticle($number);

        $group = $this->createCustomerGroup('TE', 0);
        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $article = $this->getApi()->create($data);

        $translation = array(
            'name' => 'TEST-EN',
            'description' => 'TEST-EN',
            'description_long' => 'TEST-EN',
            'additionaltext' => 'TEST-EN'
        );
        $this->getTranslationApi()->create(array(
            'type' => 'article',
            'localeId' => 2,
            'key' => $article->getId(),
            'data' => $translation
        ));

        $this->switchState($group, $this->getShop(2), $this->getHighTax());

        $promotion = $this->getPromotion($number);

        $this->assertEquals('TEST-EN', $promotion['articleName']);
        $this->assertEquals('TEST-EN', $promotion['description']);
        $this->assertEquals('TEST-EN', $promotion['description_long']);
        $this->assertEquals('TEST-EN', $promotion['additionaltext']);

        $this->removeArticle($number);
    }

    /**
     * This test is used for the core refactoring to validate if
     * all data are implemented in the compatibility layer
     */
    public function testArrayKeys()
    {
        $keys = array ('articleID','articleDetailsID','ordernumber','datum','sales','highlight',
                       'description','description_long','supplierName','supplierImg','articleName',
                       'taxID','price','pseudoprice','tax','attr1','attr2','attr3','attr4','attr5','attr6',
                       'attr7','attr8','attr9','attr10','attr11','attr12','attr13','attr14','attr15','attr16','attr17',
                       'attr18','attr19','attr20','instock','weight','shippingtime','pricegroup','pricegroupID',
                       'pricegroupActive','filtergroupID','purchaseunit','referenceunit','unitID','length','height','width',
                       'laststock','additionaltext','sConfigurator','esd','sVoteAverange','newArticle','topseller',
                       'sUpcoming','sReleasedate','sVariantArticle','sReleaseDate','priceStartingFrom','pseudopricePercent','image',
                       'linkBasket','linkDetails','mode');

        $number = 'Key-1';
        $this->removeArticle($number);
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $this->getApi()->create($data);

        $this->switchState($group, $this->getShop(), $this->getHighTax());

        $promotion = $this->getPromotion($number);

        foreach($keys as $key) {
            $this->assertArrayHasKey($key, $promotion);
        }

        $this->removeArticle($number);
    }

    public function testPropertyTranslation()
    {
        $number = 'Properties-1';
        $this->removeArticle($number);
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getSimplePrices('TE');

        $properties = $this->createProperties();

        $data = array_merge(
            $data,
            $properties
        );

        $this->getApi()->create($data);

        $this->switchState($group, $this->getShop(2), $this->getHighTax());
        $promotion = $this->getPromotion($number);

        $this->assertArrayHasKey('sProperties', $promotion);

        $properties = $promotion['sProperties'];
        $this->assertCount(2, $properties);

        $properties = array_values($properties);

        $this->assertEquals('TEST-EN', $properties[0]['name']);
        $this->assertEquals('TEST-EN', $properties[0]['groupName']);

        foreach($properties[0]['values'] as $value) {
            $this->assertEquals($value, 'TEST-EN');
        }

        $this->assertEquals('TEST-EN', $properties[1]['name']);
        $this->assertEquals('TEST-EN', $properties[1]['groupName']);

        $this->removeArticle($number);
    }


}
