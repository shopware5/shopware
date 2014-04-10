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

class sArticles_PromotionByIdTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    private function getDetailRepo()
    {
        return Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * @return \Shopware\Components\Api\Resource\Article
     */
    private function getApi()
    {
        $api = new \Shopware\Components\Api\Resource\Article();
        $api->setManager(Shopware()->Models());
        return $api;
    }

    /**
     * @return \Shopware\Components\Api\Resource\Translation
     */
    private function getTranslationApi()
    {
        $api = new \Shopware\Components\Api\Resource\Translation();
        $api->setManager(Shopware()->Models());
        return $api;
    }

    private function removeArticle($number)
    {
        $article = $this->getDetailRepo()->findOneBy(
            array('number' => $number)
        );
        if ($article) {
            Shopware()->Models()->remove($article);
            Shopware()->Models()->flush($article);
            Shopware()->Models()->clear();
        }
    }

    private function getPromotion($number)
    {
        return Shopware()->Modules()->Articles()->sGetPromotionById('fix', null, $number);
    }


    private function switchCustomerGroup($groupKey)
    {
        $system = Shopware()->Modules()->Articles()->sSYSTEM;

        $system->sUSERGROUP = $groupKey;

        $group = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_customergroups WHERE groupkey = ?',
            array($groupKey)
        );

        $system->sUSERGROUPDATA = $group;

        Shopware()->Modules()->Articles()->sSYSTEM = $system;
    }

    private function switchShop($id)
    {
        $shop = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $id);

        if (!$shop) {
            throw new Exception(sprintf("Shop not found in unit test! id: %s", $id));
        }

        $shop->registerResources(Shopware()->Bootstrap());
        Shopware()->Modules()->Articles()->translationId = $shop->getId();
    }



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
            $this->getArticleProperties()
        );

        $this->getApi()->create($data);

        $this->switchState($group, $this->getShop(), $this->getHighTax());

        $promotion = $this->getPromotion($number);

        $this->assertArrayHasKey('sProperties', $promotion);

        $properties = $promotion['sProperties'];
        $this->assertCount(2, $properties);

        $properties = array_values($properties);

        $this->assertEquals('Edelbrände', $properties[0]['groupName']);
        $this->assertEquals('Farbe', $properties[0]['name']);
        $this->assertCount(4, $properties[0]['values']);


        $this->assertEquals('Edelbrände', $properties[1]['groupName']);
        $this->assertEquals('Alkoholgehalt', $properties[1]['name']);
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

        $properties = $this->getArticleProperties();

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

        $translations = array('gold', 'transparent', 'red', 'chocolate brown');

        $this->assertEquals('Color', $properties[0]['name']);
        $this->assertEquals('Brandy', $properties[0]['groupName']);

        foreach($properties[0]['values'] as $value) {
            $this->assertContains($value, $translations);
        }

        $this->assertEquals('Alcohol in %', $properties[1]['name']);
        $this->assertEquals('Brandy', $properties[1]['groupName']);

        $this->removeArticle($number);
    }

    private function getSimpleConfiguratorSet($groupLimit = 3, $optionLimit = 5)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('groups.id', 'groups.name'))
            ->from('Shopware\Models\Article\Configurator\Group', 'groups')
            ->setFirstResult(0)
            ->setMaxResults($groupLimit)
            ->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('options.id', 'options.name'))
            ->from('Shopware\Models\Article\Configurator\Option', 'options')
            ->where('options.groupId = :groupId')
            ->setFirstResult(0)
            ->setMaxResults($optionLimit)
            ->orderBy('options.position', 'ASC');

        foreach($groups as &$group) {
            $builder->setParameter('groupId', $group['id']);
            $group['options'] = $builder->getQuery()->getArrayResult();
        }

        return array(
            'name' => 'Test-Set',
            'groups' => $groups
        );
    }

    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     * @param $groups
     * @param array $groupMapping
     * @param array $optionMapping
     * @return array
     */
    private function createConfiguratorVariants(
        $groups,
        $groupMapping = array('key' => 'groupId', 'value' => 'id'),
        $optionMapping = array('key' => 'option', 'value' => 'name')
    )
    {
        $options = array();

        $groupArrayKey = $groupMapping['key'];
        $groupValuesKey = $groupMapping['value'];
        $optionArrayKey = $optionMapping['key'];
        $optionValuesKey = $optionMapping['value'];

        foreach($groups as $group) {
            $groupOptions = array();
            foreach($group['options'] as $option) {
                $groupOptions[] = array(
                    $groupArrayKey => $group[$groupValuesKey],
                    $optionArrayKey => $option[$optionValuesKey]
                );
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->combinations($options);
        $combinations = $this->cleanUpCombinations($combinations);

        $variants = array();
        foreach($combinations as $combination) {
            $variant = $this->getSimpleDetail();
            $variant['prices'] = $this->getSimplePrices('EK');
            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;
        }
        return $variants;
    }

    /**
     * Combinations merge the result of dimensional arrays not perfectly
     * so we have to clean up the first array level.
     * @param $combinations
     * @return mixed
     */
    protected function cleanUpCombinations($combinations) {

        foreach($combinations as &$combination) {
            $combination[] = array(
                'option' => $combination['option'],
                'groupId' => $combination['groupId']
            );
            unset($combination['groupId']);
            unset($combination['option']);
        }

        return $combinations;
    }

    /**
     * Helper function which combines all array elements
     * of the passed arrays.
     *
     * @param $arrays
     * @param int $i
     * @return array
     */
    protected function combinations($arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }


    private function createArticleVotes($articleId, $votePoints = array(3,4,5))
    {
        Shopware()->Db()->executeUpdate(
            "DELETE FROM s_articles_vote WHERE articleID = ?",
            array($articleId)
        );
        foreach ($votePoints as $point) {
            Shopware()->Db()->insert('s_articles_vote', array(
                'articleID' => $articleId,
                'name' => 'Test',
                'points' => $point,
                'active' => true
            ));
        }

    }


    private function createCustomerGroup($key, $discount = 10)
    {
        $this->removeCustomerGroup($key);

        $customer = new \Shopware\Models\Customer\Group();
        $data = $this->getCustomerGroupData($key, $discount);

        $customer->fromArray($data);

        Shopware()->Models()->persist($customer);
        Shopware()->Models()->flush($customer);
        Shopware()->Models()->clear();

        return $customer;
    }

    private function removeCustomerGroup($key)
    {
        $ids = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_core_customergroups WHERE groupkey = ?',
            array($key)
        );

        if (!$ids) {
            return;
        }

        foreach ($ids as $id) {
            $customer = Shopware()->Models()->find('Shopware\Models\Customer\Group', $id);

            if (!$customer) {
                continue;
            }

            Shopware()->Models()->remove($customer);
            Shopware()->Models()->flush($customer);
        }
        Shopware()->Models()->clear();
    }


    private function getScaledPrices($group = 'EK', $priceOffset = 0)
    {
        return array(
            array(
                'from' => 1,
                'to' => 10,
                'price' => $priceOffset + 1000.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 1200
            ),
            array(
                'from' => 11,
                'to' => 20,
                'price' => $priceOffset + 750.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 950
            ),
            array(
                'from' => 21,
                'to' => 'beliebig',
                'price' => $priceOffset + 500.00,
                'customerGroupKey' => $group,
                'pseudoPrice' => $priceOffset + 700
            )
        );
    }


    private function getSpecifyMedia($id)
    {
        return Shopware()->Models()->find('Shopware\Models\Media\Media', $id);
    }

    private function getBaseData()
    {
        return array(
            'name' => 'Refactor test',
            'supplierId' => 1,
            'taxId' => 1,
            'active' => true
        );
    }

    private function getSimpleDetail($number = null)
    {
        if ($number === null) $number = 'TEST' . uniqid();

        return array(
            'number' => $number,
            'inStock' => 200,
            'active' => true
        );
    }

    private function getCustomerGroupData($key, $discount)
    {
        return array(
            'key' => $key,
            'name' => 'Test group',
            'tax' => true,
            'taxInput' => true,
            'mode' => true,
            'discount' => $discount
        );
    }

    private function getSimplePrices($group = 'EK')
    {
        return array(
            array(
                'from' => 1,
                'to' => 'beliebig',
                'price' => 119,
                'pseudoPrice' => 200,
                'customerGroupKey' => $group
            )
        );
    }

    private function getMedia($limit = 1)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('DISTINCT media.id as mediaId')
            ->from('Shopware\Models\Media\Media', 'media')
            ->setFirstResult(0)
            ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    private function getArticleProperties($id = 1, $optionCount = 2, $valueCount = 5)
    {
        $groups = Shopware()->Db()->fetchCol('SELECT id FROM s_filter_options LIMIT ' . $optionCount);

        $values = array();
        foreach ($groups as $groupId) {
            $values = array_merge(
                $values,
                Shopware()->Db()->fetchAll(
                    "SELECT id, optionID
                    FROM s_filter_values
                    WHERE optionID = ?
                    ORDER BY id LIMIT " . $valueCount,
                    array($groupId)
                )
            );
        }

        return array(
            'filterGroupId' => $id,
            'propertyValues' => $values
        );

    }





















    protected function getHighTax()
    {
        return Shopware()->Models()->find(
            'Shopware\Models\Tax\Tax',
            1
        );
    }


    /**
     * @param int $shopId
     * @return \Shopware\Models\Shop\Shop
     */
    protected function getShop($shopId = 1)
    {
        return Shopware()->Models()->find(
            'Shopware\Models\Shop\Shop',
            $shopId
        );
    }


    protected function switchState(
        \Shopware\Models\Customer\Group $group,
        \Shopware\Models\Shop\Shop $shop,
        \Shopware\Models\Tax\Tax $tax
    ) {
        $state = new \Shopware\Struct\GlobalState();

        $customerGroup = new \Shopware\Struct\CustomerGroup();
        $customerGroup->setKey($group->getKey());
        $customerGroup->setUseDiscount(true);
        $customerGroup->setId($group->getId());
        $customerGroup->setPercentageDiscount($group->getDiscount());
        $customerGroup->setDisplayGross($group->getTax());

        $state->setCurrentCustomerGroup($customerGroup);
        $state->setFallbackCustomerGroup($customerGroup);

        $state->setCurrency(new \Shopware\Struct\Currency());
        $state->getCurrency()->setFactor(1);

        $state->setShop(new \Shopware\Struct\Shop());
        $state->getShop()->setId($shop->getId());

        $state->setTax(new \Shopware\Struct\Tax());
        $state->getTax()->setId($tax->getId());
        $state->getTax()->setTax($tax->getTax());

        $service = $this->getGlobalStateService();

        $service->expects($this->any())
            ->method('get')
            ->will($this->returnValue($state));

        Shopware()->Modules()->Articles()->globalStateService = $service;



        $this->switchCustomerGroup($customerGroup->getKey());
        $this->switchShop($shop->getId());
    }


    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getGlobalStateService()
    {
        return $this->getMockBuilder('Shopware\Service\GlobalState')
            ->disableOriginalConstructor()
            ->getMock();
    }

}
