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
                        'name' => 'neueOption'.uniqid()
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
                            array( 'name' => 'Gelb'),
                            array( 'name' => 'grün')
                        )
                    ),
                    array(
                        'name' => 'Gräße',
                        'options' => array(
                            array( 'name' => 'L'),
                            array( 'name' => 'XL')
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
            'taxId'      => 1,


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
        /**@var $article \Shopware\Models\Article\Article*/
        $updated = $this->resource->update($article->getId(), $updateArticle);
        $this->assertEquals($updated->getName(), 'Turnschuhe', "Article name don't match");

        /**@var $variant \Shopware\Models\Article\Detail*/
        foreach($updated->getDetails() as $variant) {
            $this->assertTrue(in_array($variant->getNumber(), array('turn', 'turn.1', 'turn.2', 'turn.3'), 'Variant number dont match'));

            $this->assertArrayCount(2, $variant->getConfiguratorOptions(), 'Configurator option count dont match');

            /**@var $option \Shopware\Models\Article\Configurator\Option*/
            foreach($variant->getConfiguratorOptions() as $option) {
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


    public function testCreateUseConfiguratorId()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('PARTIAL groups.{id}', 'PARTIAL options.{id}'))
                ->from('Shopware\Models\Article\Configurator\Group', 'groups')
                ->innerJoin('groups.options', 'options')
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.position', 'ASC')
                ->setFirstResult(0)
                ->setMaxResults(2);

        $query = $builder->getQuery();
        $query->setHydrationMode(\Shopware\Components\Api\Resource\Article::HYDRATE_ARRAY);
        $paginator = Shopware()->Models()->createPaginator($query);

        $configurator = $paginator->getIterator()->getArrayCopy();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('options.id as optionId', 'options.groupId as groupId'))
            ->from('Shopware\Models\Article\Configurator\Option', 'options')
            ->addOrderBy('options.position', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(5);

        $options = $builder->getQuery()->getArrayResult();

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
                    /**
                     * 0 =>
                     *  array (
                     *      'optionId' => 11,
                     *      'groupId' => 5,
                     *  ),
                     * 1 =>
                     *  array (
                     *      'optionId' => 35,
                     *      'groupId' => 5,
                     *  )
                     */
                    'configuratorOptions' => $options
                )
            ),
            /**
             * array(
             *    'name' => 'Test-Set'
             *    'groups' => array(
             *        'id' => 5,
             *        'options' = array(
             *            'id' => 11
             *        )
             *    )
             * )
             */
            'configuratorSet' => array(
                'name' => 'Test-Set',
                'groups' => $configurator
            )
        );

        $article = $this->resource->create($testData);

        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Article::HYDRATE_ARRAY);
        $data = $this->resource->getOne($article->getId());

        $this->assertArrayCount(5, $data['details'][0]['configuratorOptions']);

        return $variantNumber;
    }

    /**
     * @depends testCreateUseConfiguratorId
     */
    public function testUpdateUseConfiguratorIds($variantNumber) {

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('options.id as optionId', 'options.groupId as groupId'))
            ->from('Shopware\Models\Article\Configurator\Option', 'options')
            ->addOrderBy('options.position', 'ASC')
            ->setFirstResult(2)
            ->setMaxResults(2);

        $options = $builder->getQuery()->getArrayResult();

        $id = Shopware()->Db()->fetchOne("SELECT articleID FROM s_articles_details WHERE ordernumber = ?", array($variantNumber));

        $data = array(
            'variants' => array(
                array(
                    'number' => $variantNumber,
                    'configuratorOptions' => $options
                )
            )
        );

        $this->resource->update($id, $data);

        $data = $this->resource->getOne($id);
        $this->assertArrayCount(2, $data['details'][0]['configuratorOptions']);
    }

}
