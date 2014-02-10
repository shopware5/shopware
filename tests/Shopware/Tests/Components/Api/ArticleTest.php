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
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Api_ArticleTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Article
     */
    protected $resource;

    /**
     * @return \Shopware\Components\Api\Resource\Article
     */
    public function createResource()
    {
        return new \Shopware\Components\Api\Resource\Article();
    }

    /**
     * @group performance
     */
    public function testPerformanceGetOneArray()
    {
        $ids = Shopware()->Db()->fetchCol("SELECT DISTINCT id FROM s_articles");
        $this->resource->setResultMode(Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY);
        foreach ($ids as $id) {
            $article = $this->resource->getOne($id);
            $this->assertInternalType('array', $article);
        }
    }

    /**
     * @group performance
     */
    public function testPerformanceGetOneObject()
    {
        $ids = Shopware()->Db()->fetchCol("SELECT DISTINCT id FROM s_articles");
        $this->resource->setResultMode(Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
        foreach ($ids as $id) {
            $article = $this->resource->getOne($id);
            $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        }
    }

    /**
     * @group performance
     *
     * @return int
     */
    public function testPerformanceCreateBigOne()
    {
        $article = $this->createBigArticle();
        return $article->getId();
    }

    /**
     * @group performance
     *
     * @depends testPerformanceCreateBigOne
     */
    public function testPerformanceGetBigOneObject($id)
    {
        $this->resource->setResultMode(Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);

        $data = $this->resource->getOne($id);
        $this->assertInstanceOf('\Shopware\Models\Article\Article', $data);
    }

    /**
     * @group performance
     *
     * @depends testPerformanceCreateBigOne
     */
    public function testPerformanceGetBigOneArray($id)
    {
        $this->resource->setResultMode(Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY);

        $data = $this->resource->getOne($id);
        $this->assertInternalType('array', $data);
    }

    /**
     * @group performance
     *
     * @depends testPerformanceCreateBigOne
     */
    public function testPerformanceBigArticleUpdate($id)
    {
        for($i=0; $i < 20; $i++) {
            $data = array(
                'similar' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_articles ORDER BY RAND() LIMIT 10"),
                'categories' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_categories ORDER BY RAND() LIMIT 20"),
                'related' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_articles LIMIT 10"),
            );

            $article = $this->resource->update($id, $data);

            $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        }
    }

    public function testCreateShouldBeSuccessful()
    {
        $testData = array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'metaTitle' => 'this is a test title with umlauts äöüß',

            'filterGroupId' => 1,

            'propertyValues' => array(
                array(
                    'value' => 'grün',
                    'option' => array(
                        'name' => 'Farbe'
                    )
                ),
                array(
                    'value' => 'testWert',
                    'option' => array(
                        'name' => 'neueOption' . uniqid()
                    )
                )
            ),

            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(),
                'inStock' => 15,
                'unitId' => 1,

                'attribute' => array(
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ),

                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'purchaseSteps' => 2,

                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ),
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ),
                )
            ),

            'configuratorSet' => array(
                'name' => 'MeinKonf',
                'groups' => array(
                    array(
                        'name' => 'Farbe',
                        'options' => array(
                            array('name' => 'Gelb'),
                            array('name' => 'grün')
                        )
                    ),
                    array(
                        'name' => 'Gräße',
                        'options' => array(
                            array('name' => 'L'),
                            array('name' => 'XL')
                        )
                    ),
                )
            ),

            'variants' => array(
                array(
                    'number' => 'swTEST.variant.' . uniqid(),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => array(
                        'unit' => 'xyz',
                        'name' => 'newUnit'
                    ),

                    'attribute' => array(
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ),

                    'configuratorOptions' => array(
                        array(
                            'option' => 'Gelb',
                            'group' => 'Farbe'
                        ),
                        array(
                            'option' => 'XL',
                            'group' => 'Größe'
                        )

                    ),

                    'minPurchase' => 5,
                    'purchaseSteps' => 2,

                    'prices' => array(
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ),
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ),
                    )

                )
            ),
            'taxId' => 1,
            'supplierId' => 2,
            'similar' => array(
                array('id' => 5),
                array('id' => 6),
            ),
            'categories' => array(
                array('id' => 15),
                array('id' => 10),
            ),
            'related' => array(
                array('id' => 3, 'cross' => true),
                array('id' => 4),
            ),
            'links' => array(
                array('name' => 'foobar', 'link' => 'http://example.org'),
                array('name' => 'Video', 'link' => 'http://example.org'),
            ),
        );

        $article = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());

        $this->assertEquals($article->getName(), $testData['name']);
        $this->assertEquals($article->getDescription(), $testData['description']);
        $this->assertEquals($article->getMetaTitle(), $testData['metaTitle']);

        $this->assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);
        $this->assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        $this->assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );


        $propertyValues = $article->getPropertyValues()->getValues();
        $this->assertEquals(count($propertyValues), count($testData['propertyValues']));
        foreach ($propertyValues as $propertyValue) {
            $this->assertContains($propertyValue->getValue(), array("grün", "testWert"));
        }

        $this->assertEquals($testData['taxId'], $article->getTax()->getId());

        $this->assertEquals(2, count($article->getCategories()));
        $this->assertEquals(2, count($article->getRelated()));
        $this->assertEquals(2, count($article->getSimilar()));
        $this->assertEquals(2, count($article->getLinks()));
        $this->assertEquals(2, count($article->getMainDetail()->getPrices()));

        return $article->getId();
    }

    /**
     * Test creating an article with new configurator set and multiple variants
     * SW-7925
     *
     * @return int
     */
    public function testCreateWithVariantsAndNewConfiguratorSetShouldBeSuccessful()
    {
        $testData = array(
            'name' => 'Test article',
            'description' => 'Test description',
            'descriptionLong' => 'Long test description',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'metaTitle' => 'this is a test title with umlauts äöüß',

            'filterGroupId' => 1,

            'propertyValues' => array(
                array(
                    'value' => 'grün',
                    'option' => array(
                        'name' => 'Farbe'
                    )
                ),
                array(
                    'value' => 'testWert',
                    'option' => array(
                        'name' => 'neueOption' . uniqid()
                    )
                )
            ),

            'mainDetail' => array(
                'number' => 'swConfigSetMainTest' . uniqid(),
                'inStock' => 15,
                'unitId' => 1,

                'attribute' => array(
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ),

                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'purchaseSteps' => 2,

                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ),
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ),
                )
            ),

            'configuratorSet' => array(
                'name' => 'NewConfigSet',
                'groups' => array(
                    array(
                        'name' => 'Group1',
                        'options' => array(
                            array('name' => 'Opt11'),
                            array('name' => 'Opt12')
                        )
                    ),
                    array(
                        'name' => 'Group2',
                        'options' => array(
                            array('name' => 'Opt21'),
                            array('name' => 'Opt22'),
                            array('name' => 'Opt23'),
                            array('name' => 'Opt24'),
                            array('name' => 'Opt25'),
                            array('name' => 'Opt26'),
                            array('name' => 'Opt27')
                        )
                    ),
                )
            ),

            'variants' => array(
                array(
                    'number' => 'swConfigSetMainTest.variant.' . uniqid(),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => array(
                        'unit' => 'xyz',
                        'name' => 'newUnit'
                    ),

                    'attribute' => array(
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ),

                    'configuratorOptions' => array(
                        array(
                            'option' => 'Opt11',
                            'group' => 'Group1'
                        ),
                        array(
                            'option' => 'Opt23',
                            'group' => 'Group2'
                        ),
                        array(
                            'option' => 'Opt24',
                            'group' => 'Group2'
                        )
                    ),

                    'minPurchase' => 5,
                    'purchaseSteps' => 2,

                    'prices' => array(
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ),
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ),
                    )
                ),
                array(
                    'number' => 'swConfigSetMainTest.variant.' . uniqid(),
                    'inStock' => 18,
                    // create another new unit
                    'unit' => array(
                        'unit' => 'xyz',
                        'name' => 'newUnit'
                    ),

                    'attribute' => array(
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ),

                    'configuratorOptions' => array(
                        array(
                            'option' => 'Opt12',
                            'group' => 'Group1'
                        ),
                        array(
                            'option' => 'Opt27',
                            'group' => 'Group2'
                        )
                    ),

                    'minPurchase' => 5,
                    'purchaseSteps' => 2,

                    'prices' => array(
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ),
                        array(
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ),
                    )
                )
            ),
            'taxId' => 1,
            'supplierId' => 2,
            'categories' => array(
                array('id' => 15),
                array('id' => 10),
            ),
            'links' => array(
                array('name' => 'foobar', 'link' => 'http://example.org'),
                array('name' => 'Video', 'link' => 'http://example.org'),
            ),
        );

        $article = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());

        $this->assertEquals($article->getName(), $testData['name']);
        $this->assertEquals($article->getDescription(), $testData['description']);
        $this->assertEquals($article->getMetaTitle(), $testData['metaTitle']);

        $this->assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);
        $this->assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        $this->assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );


        $propertyValues = $article->getPropertyValues()->getValues();
        $this->assertEquals(count($propertyValues), count($testData['propertyValues']));
        foreach ($propertyValues as $propertyValue) {
            $this->assertContains($propertyValue->getValue(), array("grün", "testWert"));
        }

        $this->assertEquals($testData['taxId'], $article->getTax()->getId());

        $this->assertEquals(2, count($article->getCategories()));
        $this->assertEquals(0, count($article->getRelated()));
        $this->assertEquals(0, count($article->getSimilar()));
        $this->assertEquals(2, count($article->getLinks()));
        $this->assertEquals(2, count($article->getMainDetail()->getPrices()));

        $groups = Shopware()->Models()
                ->getRepository('Shopware\Models\Article\Configurator\Group')
                ->findBy(array('name' => array('Group1', 'Group2')));

        foreach ($groups as $group) {
            Shopware()->Models()->remove($group);
        }

        $this->resource->delete($article);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Article::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);
        $number = $article->getMainDetail()->getNumber();

        $article = $this->resource->getOneByNumber($number);
        $this->assertEquals($id, $article->getId());

    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $article = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $article['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Article::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        // required field name is missing
        $testData = array(
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        );

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Article::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);
        $number = $article->getMainDetail()->getNumber();

        $testData = array(
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
            // update supplier id
            'supplierId' => 3,
            // categories should be replaced
            'categories' => array(
                array('id' => 16),
            ),
            'filterGroupId' => 1,
            // values should be replaced
            'propertyValues' => array(),
            // related is not included, therefore it stays untouched

            // similar is set to empty array, therefore it should be cleared
            'similar' => array(),
        );

        $article = $this->resource->updateByNumber($number, $testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertEquals($id, $article->getId());
        $this->assertEquals($article->getDescription(), $testData['description']);
        $this->assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);

        $this->assertEquals($testData['supplierId'], $article->getSupplier()->getId());

        $propertyValues = $article->getPropertyValues()->getValues();
        $this->assertEquals(count($propertyValues), count($testData['propertyValues']));

        // Categories should be updated
        $this->assertEquals(1, count($article->getCategories()));

        // Related should be untouched
        $this->assertEquals(2, count($article->getRelated()));

        // Similar should be removed
        $this->assertEquals(0, count($article->getSimilar()));

        return $number;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = array(
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
            // update supplier id
            'supplierId' => 3,
            // categories should be replaced
            'categories' => array(
                array('id' => 16),
            ),
            'filterGroupId' => 1,
            // values should be replaced
            'propertyValues' => array(),
            // related is not included, therefore it stays untouched

            // similar is set to empty array, therefore it should be cleared
            'similar' => array(),
        );

        $article = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertEquals($id, $article->getId());
        $this->assertEquals($article->getDescription(), $testData['description']);
        $this->assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);

        $this->assertEquals($testData['supplierId'], $article->getSupplier()->getId());

        $propertyValues = $article->getPropertyValues()->getValues();
        $this->assertEquals(count($propertyValues), count($testData['propertyValues']));

        // Categories should be updated
        $this->assertEquals(1, count($article->getCategories()));

        // Related should be untouched
        $this->assertEquals(2, count($article->getRelated()));

        // Similar should be removed
        $this->assertEquals(0, count($article->getSimilar()));

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id)
    {
        // required field name is blank
        $testData = array(
            'name' => ' ',
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        );

        $this->resource->update($id, $testData);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, array());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', array());
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $article = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertEquals(null, $article->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->delete(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->delete('');
    }

    /**
     * Test case to add a new article image over a media id.
     */
    public function testAddArticleMediaOverMediaId()
    {
        $this->resource->update(
            2,
            array(
                "images" => array(
                    array(
                        "articleId" => 2,
                        "mediaId" => 25,
                        "main" => 0,
                        "position" => 10000,
                    ),
                ),
            )
        );
        $article = $this->resource->getOne(2);

        $image = array_pop($article['images']);
        $this->assertEquals($image['mediaId'], 25);
    }

    public function testUpdateToVariantArticle()
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {

        }

        $article = $this->resource->create(
            array(
                'name' => 'Turnschuhe',
                'active' => true,
                'tax' => 19,
                'supplier' => 'Turnschuhe Inc.',
                'categories' => array(
                    array('id' => 15),
                ),
                'mainDetail' => array(
                    'number' => 'turn',
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                ),
            )
        );

        $updateArticle = array(
            'configuratorSet' => array(
                'groups' => array(
                    array(
                        'name' => 'Größe',
                        'options' => array(
                            array('name' => 'S'),
                            array('name' => 'M')
                        )
                    ),
                    array(
                        'name' => 'Farbe',
                        'options' => array(
                            array('name' => 'grün'),
                            array('name' => 'blau')
                        )
                    ),
                )
            ),
            'taxId' => 1,


            'variants' => array(
                array(
                    'isMain' => true,
                    'number' => 'turn',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / grün',
                    'configuratorOptions' => array(
                        array('group' => 'Größe', 'option' => 'S'),
                        array('group' => 'Farbe', 'option' => 'grün'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 1999,
                        ),
                    )
                ),
                array(
                    'isMain' => false,
                    'number' => 'turn.1',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / blau',
                    'configuratorOptions' => array(
                        array('group' => 'Größe', 'option' => 'S'),
                        array('group' => 'Farbe', 'option' => 'blau'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                ),
                array(
                    'isMain' => false,
                    'number' => 'turn.2',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / grün',
                    'configuratorOptions' => array(
                        array('group' => 'Größe', 'option' => 'M'),
                        array('group' => 'Farbe', 'option' => 'grün'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                ),
                array(
                    'isMain' => false,
                    'number' => 'turn.3',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / blau',
                    'configuratorOptions' => array(
                        array('group' => 'Größe', 'option' => 'M'),
                        array('group' => 'Farbe', 'option' => 'blau'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                )
            )
        );
        /**@var $article \Shopware\Models\Article\Article */
        $updated = $this->resource->update($article->getId(), $updateArticle);
        $this->assertEquals($updated->getName(), 'Turnschuhe', "Article name don't match");

        /**@var $variant \Shopware\Models\Article\Detail */
        foreach ($updated->getDetails() as $variant) {
            $this->assertTrue(in_array($variant->getNumber(), array('turn', 'turn.1', 'turn.2', 'turn.3'), 'Variant number dont match'));

            $this->assertCount(2, $variant->getConfiguratorOptions(), 'Configurator option count dont match');

            /**@var $option \Shopware\Models\Article\Configurator\Option */
            foreach ($variant->getConfiguratorOptions() as $option) {
                $this->assertTrue(in_array($option->getName(), array('M', 'S', 'blau', 'grün')));
            }
        }

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {

        }

    }

    /**
     *
     */
    public function testCreateUseConfiguratorId()
    {
        $configurator = $this->getSimpleConfiguratorSet(2, 5);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);
        $variantNumber = 'swVariant' . uniqid();

        $testData = array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 1,
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => array(
                    array('customerGroupKey' => 'EK','from' => 1,'to' => '-','price' => 400)
                )
            ),
            'variants' => array(
                array(
                    'number' => $variantNumber,
                    'inStock' => 15,
                    'unitId' => 1,
                    'prices' => array(
                        array('customerGroupKey' => 'EK','from' => 1,'to' => '-','price' => 400)
                    ),
                    'configuratorOptions' => $variantOptions
                )
            ),
            'configuratorSet' => $configurator
        );

        $article = $this->resource->create($testData);

        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Article::HYDRATE_ARRAY);
        $data = $this->resource->getOne($article->getId());


        $this->assertCount(2, $data['details'][0]['configuratorOptions']);

        return $variantNumber;
    }

    /**
     * @depends testCreateUseConfiguratorId
     */
    public function testUpdateUseConfiguratorIds($variantNumber) {

        $configurator = $this->getSimpleConfiguratorSet(2, 5);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);

        $id = Shopware()->Db()->fetchOne("SELECT articleID FROM s_articles_details WHERE ordernumber = ?", array($variantNumber));

        $data = array(
            'variants' => array(
                array(
                    'number' => $variantNumber,
                    'configuratorOptions' => $variantOptions
                )
            )
        );

        $this->resource->update($id, $data);

        $data = $this->resource->getOne($id);
        $this->assertCount(2, $data['details'][0]['configuratorOptions']);
    }

    public function testCreateWithMainImages()
    {
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT
        );

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array(
            'media.id as mediaId',
            '2 as main'
        ))
            ->from('Shopware\Models\Media\Media', 'media')
            ->addOrderBy('media.id', 'ASC')
            ->setFirstResult(5)
            ->setMaxResults(4);

        /**
         * Get random images.
         * Only want to check if the main flag will be used.
         */
        $images = $builder->getQuery()->getArrayResult();
        $images[2]['main'] = 1;
        $expectedMainId = $images[2]['mediaId'];

        $data = $this->getSimpleTestData();
        $data['images'] = $images;
        $article = $this->resource->create($data);

        $this->assertCount(4, $article->getImages());

        $mainFlagExists = false;

        /**@var $image \Shopware\Models\Article\Image*/
        foreach($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $mainFlagExists = true;
                $this->assertEquals($expectedMainId, $image->getMedia()->getId());
            }
        }
        $this->assertTrue($mainFlagExists);
        return $article->getId();
    }

    /**
     * @depends testCreateWithMainImages
     */
    public function testUpdateWithSingleMainImage($articleId)
    {
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = array();
        $newId = null;
        foreach($article['images'] as $image) {
            if ($image['main'] !== 1) {
                $updateImages['images'][] = array(
                    'id' => $image['id'],
                    'main' => 1
                );
                $newId = $image['id'];
                break;
            }
        }
        $article = $this->resource->update($articleId, $updateImages);

        $this->assertCount(4, $article->getImages());

        $hasMain = false;
        foreach($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                $this->assertEquals($image->getId(), $newId);
            }
        }
        $this->assertTrue($hasMain);

        return $article->getId();
    }

    /**
     * @depends testUpdateWithSingleMainImage
     */
    public function testUpdateWithMainImage($articleId)
    {
        $this->resource->getManager()->clear();

        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = array();
        $lastMainId = null;

        foreach($article['images'] as $image) {
            $newImageData = array(
                'id' => $image['id'],
                'main' => $image['main']
            );

            if ($image['main'] == 1) {
                $lastMainId = $image['id'];
                $newImageData['main'] = 2;
            }

            $updateImages['images'][] = $newImageData;
        }

        $newMainId = null;
        foreach($updateImages['images'] as &$image) {
            if ($image['id'] !== $lastMainId) {
                $image['main'] = 1;
                $newMainId = $image['id'];
                break;
            }
        }
        $article = $this->resource->update($articleId, $updateImages);
        $this->assertCount(4, $article->getImages());

        $hasMain = false;
        /**@var $image \Shopware\Models\Article\Image*/
        foreach($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                $this->assertEquals($newMainId, $image->getId());
            }
        }
        $this->assertTrue($hasMain);
    }

    /**
     * This unit test, tests if the attribute fields are translatable.
     */
    public function testCreateTranslation()
    {
        $data = $this->getSimpleTestData();

        $definedTranslation = array(
            array(
                'shopId' => 2,
                'name' => 'English-Name',
                'description' => 'English-Description',
                'descriptionLong' => 'English-DescriptionLong',
                'keywords' => 'English-Keywords',
                'packUnit' => 'English-PackUnit'
            )
        );

        for($i=1; $i<=20; $i++) {
            $definedTranslation[0]['attr' . $i] = 'English-Attr' . $i;
        }

        $data['translations'] = $definedTranslation;

        $article = $this->resource->create($data);
        $newData = $this->resource->getOne($article->getId());

        $savedTranslation = $newData['translations'][2];
        $definedTranslation = $definedTranslation[0];

        $this->assertEquals($definedTranslation['name'], $savedTranslation['name']);
        $this->assertEquals($definedTranslation['description'], $savedTranslation['description']);
        $this->assertEquals($definedTranslation['descriptionLong'], $savedTranslation['descriptionLong']);
        $this->assertEquals($definedTranslation['keywords'], $savedTranslation['keywords']);
        $this->assertEquals($definedTranslation['packUnit'], $savedTranslation['packUnit']);

        for($i=1; $i<=20; $i++) {
            $attr = 'attr' . $i;
            $this->assertEquals($definedTranslation[$attr], $savedTranslation[$attr]);
        }
    }

    public function testBase64ImageUpload()
    {
        $data = $this->getSimpleTestData();

        $data['images'] = array(
            array(
                'link' => 'data:image/png;base64,' . require_once(__DIR__ . '/fixtures/base64image.php')
            )
        );

        $model = $this->resource->create($data);
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($model->getId());

        $mediaPath = Shopware()->DocPath('media_image');

        $this->assertCount(count($data['images']), $article['images']);
        foreach($article['images'] as $image) {
            $this->assertFileExists($mediaPath . $image['path'] . '.' . $image['extension']);
            $this->assertEquals('image/png', mime_content_type($mediaPath . $image['path'] . '.' . $image['extension']));
        }
    }

    public function testImageReplacement()
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $createdIds = Shopware()->Db()->fetchCol('SELECT id FROM s_articles_img WHERE articleID = :articleId', array(
            ':articleId' => $article->getId()
        ));

        $data = array(
            '__options_images' => array('replace' => true),
            'images' => $this->getImagesForNewArticle(100)
        );

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol('SELECT id FROM s_articles_img WHERE articleID = :articleId', array(
            ':articleId' => $article->getId()
        ));

        foreach($updateIds as $id) {
            $this->assertNotContains($id, $createdIds);
        }
        $this->assertCount(5, $updateIds);
    }

    public function testImageReplacementMerge()
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $data = array(
            '__options_images' => array('replace' => false),
            'images' => $this->getImagesForNewArticle(40)
        );

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol('SELECT id FROM s_articles_img WHERE articleID = :articleId', array(
            ':articleId' => $article->getId()
        ));

        $this->assertCount(10, $updateIds);
    }

    public function testImageReplacementWithoutOption()
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $data = array(
            'images' => $this->getImagesForNewArticle(40)
        );

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol('SELECT id FROM s_articles_img WHERE articleID = :articleId', array(
            ':articleId' => $article->getId()
        ));

        $this->assertCount(10, $updateIds);
    }


    public function testImageAttributes()
    {
        $data = $this->getSimpleTestData();
        $images = $this->getImagesForNewArticle();
        foreach($images as &$image) {
            $image['attribute'] = array(
                'attribute1' => 'attr1'
            );
        }
        $data['images'] = $images;
        $article = $this->resource->create($data);

        /**@var $image \Shopware\Models\Article\Image*/
        foreach($article->getImages() as $image) {
            $this->assertInstanceOf('\Shopware\Models\Attribute\ArticleImage', $image->getAttribute());
            $this->assertEquals('attr1', $image->getAttribute()->getAttribute1());
            $this->assertEquals(null, $image->getAttribute()->getAttribute2());
            $this->assertEquals(null, $image->getAttribute()->getAttribute3());
        }
    }

    public function testCreateWithDuplicateProperties()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('values', 'option'))
            ->from('Shopware\Models\Property\Value', 'values')
            ->innerJoin('values.option', 'option')
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = array();
        foreach ($databaseValues as $value) {
            $valueIds[] = $value['id'];
            $optionIds[] = $value['option']['id'];
            $properties[] = array(
                'value' => $value['value'],
                'option' => array(
                    'name' => $value['option']['name']
                )
            );
        }
        $data = $this->getSimpleTestData();
        $data['propertyValues'] = $properties;
        $data['filterGroupId'] = 1;
        $article = $this->resource->create($data);
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($article->getId());
        foreach ($article['propertyValues'] as $value) {
            $this->assertTrue(in_array($value['id'], $valueIds));
            $this->assertTrue(in_array($value['optionId'], $optionIds));
        }
    }

    public function testUpdateWithDuplicateProperties()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('values', 'option'))
            ->from('Shopware\Models\Property\Value', 'values')
            ->innerJoin('values.option', 'option')
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = array();
        foreach ($databaseValues as $value) {
            $valueIds[] = $value['id'];
            $optionIds[] = $value['option']['id'];
            $properties[] = array(
                'value' => $value['value'],
                'option' => array(
                    'name' => $value['option']['name']
                )
            );
        }
        $update = array(
            'propertyValues' => $properties,
            'filterGroupId' => 1
        );
        $data = $this->getSimpleTestData();
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT
        );
        $article = $this->resource->create($data);
        /**@var $article Shopware\Models\Article\Article */
        $article = $this->resource->update($article->getId(), $update);
        /**@var $value \Shopware\Models\Property\Value*/
        foreach ($article->getPropertyValues() as $value) {
            $this->assertTrue(in_array($value->getId(), $valueIds));
            $this->assertTrue(in_array($value->getOption()->getId(), $optionIds));
        }
    }

    public function testPriceReplacement() {
        $data = $this->getSimpleTestData();
        $article = $this->resource->create($data);

        $update = array(
            'mainDetail' => array(
                'number' => $article->getMainDetail()->getNumber(),
                '__options_prices' => array('replace' => false),
                'prices' => array(
                    array(
                        'customerGroupKey' => 'H',
                        'from' => 1,
                        'to' => '10',
                        'price' => 200,
                    ),
                    array(
                        'customerGroupKey' => 'H',
                        'from' => 11,
                        'to' => '-',
                        'price' => 100,
                    )
                )
            )
        );

        $article = $this->resource->update($article->getId(), $update);
        $this->assertCount(3, $article->getMainDetail()->getPrices());
    }



    public function testImageConfiguration()
    {
        $this->resource->setResultMode(
            \Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT
        );

        $create = $this->getSimpleTestData();

        $images = $this->getEntityOffset(
            'Shopware\Models\Media\Media',
            0,
            1,
            array('id as mediaId')
        );

        $configurator = $this->getSimpleConfiguratorSet(1, 2);
        $variants = $this->createConfiguratorVariants($configurator['groups']);

        $usedOption = $this->getOptionsForImage($configurator, 1, 'name');
        foreach($images as &$image) {
            $image['options'] = array($usedOption);
        }

        $create['images'] = $images;
        $create['configuratorSet'] = $configurator;
        $create['variants'] = $variants;

        $article = $this->resource->create($create);

        /**@var $image \Shopware\Models\Article\Image*/
        foreach($article->getImages() as $image) {
            $this->assertCount(1, $image->getMappings());

            /**@var $mapping \Shopware\Models\Article\Image\Mapping*/
            foreach($image->getMappings() as $mapping) {
                $this->assertCount(1, $mapping->getRules());
            }
        }

        $this->resource->generateVariantImages($article->getId());

        $article = $this->resource->getOne($article->getId());

        /**@var $variant \Shopware\Models\Article\Detail*/
        foreach($article->getDetails() as $variant) {
            foreach($variant->getConfiguratorOptions() as $option) {
                if ($option->getName() == $usedOption[0]['name']) {
                    $this->assertCount(1, $variant->getImages());
                }
            }
        }
    }



    private function getOptionsForImage($configuratorSet, $optionCount = null, $property = 'id')
    {
        if (!is_int($optionCount)) {
            $optionCount = rand(1, count($configuratorSet['groups']) - 1);
        }

        $options = array();
        foreach($configuratorSet['groups'] as $group) {
            $id = rand(0, count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = array(
                $property => $option[$property]
            );
            if (count($options) == $optionCount) {
                return $options;
            }
        }
        return $options;
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
            $variant = $this->getSimpleVariantData();
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



    public function testCategoryReplacement()
    {
        $this->internalTestReplaceMode(
            'Shopware\Models\Category\Category',
            'categories',
            true
        );
        $this->internalTestReplaceMode(
            'Shopware\Models\Category\Category',
            'categories',
            false
        );
    }

    public function testSimilarReplacement()
    {
        $this->internalTestReplaceMode(
            'Shopware\Models\Article\Article',
            'similar',
            true
        );
        $this->internalTestReplaceMode(
            'Shopware\Models\Article\Article',
            'similar',
            false
        );
    }

    public function testRelatedReplacement()
    {
        $this->internalTestReplaceMode(
            'Shopware\Models\Article\Article',
            'related',
            true
        );
        $this->internalTestReplaceMode(
            'Shopware\Models\Article\Article',
            'related',
            false
        );
    }

    public function testCustomerGroupReplacement()
    {
        $this->internalTestReplaceMode(
            'Shopware\Models\Customer\Group',
            'customerGroups',
            true
        );
        $this->internalTestReplaceMode(
            'Shopware\Models\Customer\Group',
            'customerGroups',
            false
        );
    }

    private function getImagesForNewArticle($offset = 10, $limit = 5)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array(
            'media.id as mediaId',
            '2 as main'
        ))
            ->from('Shopware\Models\Media\Media', 'media', 'media.id')
            ->addOrderBy('media.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        /**
         * Get random images.
         * Only want to check if the main flag will be used.
         */
        $images = $builder->getQuery()->getArrayResult();
        $keys = array_keys($images);
        $images[$keys[0]]['main'] = 1;

        return $images;
    }

    public function testArticleDefaultPriceBehavior()
    {
        $data = $this->getSimpleTestData();

        $article = $this->resource->create($data);

        $this->assertInstanceOf('Shopware\Models\Article\Article', $article);

        /**@var $price \Shopware\Models\Article\Price*/
        $price = $article->getMainDetail()->getPrices()->first();

        $this->assertEquals(
            400 / (($article->getTax()->getTax() + 100) / 100),
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $data = $this->resource->getOne($article->getId());

        $this->assertEquals(
            400 / (($article->getTax()->getTax() + 100) / 100),
            $data['mainDetail']['prices'][0]['price']
        );
    }

    public function testSimilarWithNumber() 
    {
        $articles = $this->getEntityOffset('Shopware\Models\Article\Article', 0, 3);

        $data = $this->getSimpleTestData();
        $similar = array();
        foreach($articles as $article) {
            $model = Shopware()->Models()->find(
                'Shopware\Models\Article\Article', $article['id']
            );

            $similar[] = array('number' => $model->getMainDetail()->getNumber());
        }

        $data['similar'] = $similar;

        $article = $this->resource->create($data);

        $this->assertNotEmpty($article->getSimilar());
    }

    public function testRelatedWithNumber()
    {
        $articles = $this->getEntityOffset('Shopware\Models\Article\Article', 0, 3);

        $data = $this->getSimpleTestData();
        $similar = array();
        foreach($articles as $article) {
            $model = Shopware()->Models()->find(
                'Shopware\Models\Article\Article', $article['id']
            );

            $similar[] = array('number' => $model->getMainDetail()->getNumber());
        }

        $data['related'] = $similar;

        $article = $this->resource->create($data);

        $this->assertNotEmpty($article->getRelated());
    }


    public function testDownloads()
    {
        $data = $this->getSimpleTestData();

        $data['downloads'] = array(
            array('link' => 'data:image/png;base64,' . require_once(__DIR__ . '/fixtures/base64image.php'))
        );

        $article = $this->resource->create($data);

        $this->assertCount(1, $article->getDownloads());

        $downloads = array(
            array('id' => $article->getDownloads()->first()->getId()),
            array('link' => 'file://' . __DIR__ . '/fixtures/variant-image.png')
        );

        $update = $this->resource->update(
            $article->getId(),
            array(
                'downloads' => $downloads,
                '__options_downloads' => array('replace' => false)
            )
        );

        $this->assertCount(2, $update->getDownloads());
    }

    
    public function testArticleGrossPrices()
    {
        $data = $this->getSimpleTestData();

        $article = $this->resource->create($data);

        $this->assertInstanceOf('Shopware\Models\Article\Article', $article);

        /**@var $price \Shopware\Models\Article\Price*/
        $price = $article->getMainDetail()->getPrices()->first();

        $net = 400 / (($article->getTax()->getTax() + 100) / 100);

        $this->assertEquals(
            $net,
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $this->resource->setResultMode(2);

        $data = $this->resource->getOne(
            $article->getId(),
            array(
                'considerTaxInput' => true
            )
        );

        $price = $data['mainDetail']['prices'][0];

        $this->assertEquals(400, $price['price']);
        $this->assertEquals($net, $price['net']);
    }


    protected function internalTestReplaceMode($entity, $arrayKey, $replace = true)
    {
        //create keys for getter function and the __options parameter in the update and create
        //example => "__options_categories"  /  "getCategories"
        $replaceKey = '__options_' . $arrayKey;
        $getter = 'get' . ucfirst($arrayKey);

        //returns a simple article data set to create an article with a simple main detail
        $data = $this->getSimpleTestData();

        //get an offset of 10 entities for the current entity type, like 10x categories
        $createdEntities = $this->getEntityOffset($entity);
        $data[$arrayKey] = $createdEntities;

        $article = $this->resource->create($data);
        $this->assertCount(count($createdEntities), $article->$getter());

        $updatedEntity = $this->getEntityOffset($entity, 20, 5, array('id'));

        $update = array(
            $replaceKey => array('replace' => $replace),
            $arrayKey => $updatedEntity
        );
        $article = $this->resource->update($article->getId(), $update);

        if ($replace == true) {
            $this->assertCount(count($updatedEntity), $article->$getter());
        } else {
            $this->assertCount(count($createdEntities) + count($updatedEntity), $article->$getter());
        }
    }

    /**
     * @return \Shopware\Models\Article\Article
     */
    private function createBigArticle()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('groups', 'options'))
            ->from('Shopware\Models\Article\Configurator\Group', 'groups')
            ->innerJoin('groups.options', 'options');

        $configurator = array(
            'name' => 'Performance Test Set',
            'groups' => $builder->getQuery()->getArrayResult()
        );

        $builder->select(array(
            'groups.name as groupName',
            'options.name as option'
        ))
            ->groupBy('groups.id');

        $variantOptions = $builder->getQuery()->getArrayResult();
        foreach ($variantOptions as &$option) {
            $option['group'] = $option['groupName'];
            unset($option['groupName']);
        }

        $variants = array();
        for ($i = 0; $i < 100; $i++) {
            $variants[] = array(
                'number' => 'swTEST.variant.' . uniqid(),
                'inStock' => 17,
                'unit' => array('unit' => 'xyz', 'name' => 'newUnit'),
                'attribute' => array('attr3' => 'Freitext3', 'attr4' => 'Freitext4'),
                'configuratorOptions' => $variantOptions,
                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'prices' => array(
                    array('customerGroupKey' => 'H', 'from' => 1, 'to' => 20, 'price' => 500),
                    array('customerGroupKey' => 'H', 'from' => 21, 'to' => '-', 'price' => 400),
                )
            );
        }


        $testData = array(
            'name' => 'Performance - Artikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',

            'filterGroupId' => 1,

            'propertyValues' => array(
                array(
                    'value' => 'grün',
                    'option' => array(
                        'name' => 'Farbe'
                    )
                ),
                array(
                    'value' => 'testWert',
                    'option' => array(
                        'name' => 'neueOption' . uniqid()
                    )
                )
            ),

            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(),
                'inStock' => 15,
                'unitId' => 1,

                'attribute' => array(
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ),

                'minPurchase' => 5,
                'purchaseSteps' => 2,

                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ),
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ),
                )
            ),
            'configuratorSet' => $configurator,
            'variants' => $variants,
            'taxId' => 1,
            'supplierId' => 2,
            'similar' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_articles LIMIT 30"),
            'categories' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_categories LIMIT 100"),
            'related' => Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_articles LIMIT 30"),
            'links' => array(
                array('name' => 'foobar', 'link' => 'http://example.org'),
                array('name' => 'Video', 'link' => 'http://example.org'),
                array('name' => 'Video2', 'link' => 'http://example.org'),
                array('name' => 'Video3', 'link' => 'http://example.org'),
                array('name' => 'Video4', 'link' => 'http://example.org'),
                array('name' => 'Video5', 'link' => 'http://example.org'),
                array('name' => 'Video6', 'link' => 'http://example.org'),
                array('name' => 'Video7', 'link' => 'http://example.org'),
                array('name' => 'Video8', 'link' => 'http://example.org'),
                array('name' => 'Video9', 'link' => 'http://example.org'),
                array('name' => 'Video10', 'link' => 'http://example.org'),
            ),
        );

        $article = $this->resource->create($testData);
        Shopware()->Models()->clear();
        return $article;
    }

    private function getSimpleTestData()
    {
        return array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 400,
                    )
                )
            ),
            'taxId' => 1,
            'supplierId' => 2
        );
    }

    private function getEntityOffset($entity, $offset = 0, $limit = 10, $fields = array('id'))
    {
        if (!empty($fields)) {
            $selectFields = array();
            foreach($fields as $field) {
                $selectFields[] = 'alias.' . $field;
            }
        } else {
            $selectFields = array('alias');
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($selectFields)
                ->from($entity, 'alias')
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
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

    private function getSimpleVariantData() {
        return array(
            'number' => 'swTEST' . uniqid(),
            'inStock' => 100,
            'unitId' => 1,
            'prices' => array(
                array(
                    'customerGroupKey' => 'EK',
                    'from' => 1,
                    'to' => '-',
                    'price' => 400,
                ),
            )
        );
    }

    public function testAssignCategoriesByPathShouldBeSuccessful()
    {
        // Delete previous data
        try {
            $id = $this->resource->getIdFromNumber('hollo-1');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
        // Associate three kinds of categories with the article:
        // category by id, category by path, new category by path
        $article = $this->resource->create(
            array(
                'name' => 'Hähnchenschnitzel Hollo',
                'active' => true,
                'tax' => 19,
                'supplier' => 'Onkel Tom',
                'categories' => array(
                    array('path' => 'Deutsch|Genusswelten|Tees und Zubehör|Tees'),
                    array('path' => 'Deutsch|Genusswelten|Tees und Zubehör|Süßstoff'),
                    array('id' => 16)
                ),
                'mainDetail' => array(
                    'number' => 'hollo-1',
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 4.99,
                        ),
                    )
                ),
            )
        );
        $ids = array_map(
            function($category) {
                return $category->getId();
            },
            $article->getCategories()->toArray()
        );
        $ids = array_flip($ids);
        $this->assertArrayHasKey(12, $ids);
        $this->assertArrayHasKey(16, $ids);
        $this->assertCount(3, $ids);
    }

    public function testBatchModeShouldBeSuccessful()
    {
        $createNew = $this->getSimpleTestData();
        $updateExistingByNumber = array(
            'mainDetail' => array(
                'number' => 'SW10003'
            ),
            'keywords' => 'newKeyword1'
        );
        $updateExistingById = array(
            'id' => 3,
            'keywords' => 'newKeyword2'
        );

        $result = $this->resource->batch(array(
           'new' => $createNew,
           'existingByNumber' => $updateExistingByNumber,
           'existingById' => $updateExistingById,
        ));


        $this->assertEquals('newKeyword1', $result['existingByNumber']['data']['keywords']);
        $this->assertEquals('newKeyword2', $result['existingById']['data']['keywords']);
        $this->assertEquals('Testartikel', $result['new']['data']['name']);
    }

    public function testBatchDeleteShouldBeSuccessful()
    {

        $result = $this->resource->batch(
            array(
                $this->getSimpleTestData(),
                $this->getSimpleTestData(),
                $this->getSimpleTestData()
            )
        );

        $delete = array();
        foreach ($result as $item) {
            $delete[] = $item['data'];
        }

        $result = $this->resource->batchDelete($delete);

        $this->assertEquals(3, count($result));
    }

    private function getVariantOptionsOfSet($configuratorSet)
    {
        $options = array();
        foreach($configuratorSet['groups'] as $group) {
            $id = rand(0, count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = array(
                'optionId' => $option['id'],
                'groupId'  => $group['id']
            );
        }
        return $options;
    }

}


