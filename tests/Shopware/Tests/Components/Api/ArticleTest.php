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

            $this->assertArrayCount(2, $variant->getConfiguratorOptions(), 'Configurator option count dont match');

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
            ->setFirstResult(10)
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

        $this->assertArrayCount(4, $article->getImages());

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

        $this->assertArrayCount(4, $article->getImages());

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
        $this->assertArrayCount(4, $article->getImages());

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

        $updatedEntity = $this->getEntityOffset($entity, true, 20, 5);

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

    private function getEntityOffset($entity, $onlyId = true, $offset = 0, $limit = 10)
    {
        $fields = array('alias');
        if ($onlyId) {
            $fields = array('alias.id');
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($fields)
                ->from($entity, 'alias')
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }
}


