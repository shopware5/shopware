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
class Shopware_Tests_Components_Api_VariantTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Article
     */
    private $resourceArticle;

    /**
     * @return \Shopware\Components\Api\Resource\Variant
     */
    public function createResource()
    {
        return new \Shopware\Components\Api\Resource\Variant();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->resourceArticle = new \Shopware\Components\Api\Resource\Article();
        $this->resourceArticle->setAcl(Shopware()->Acl());
        $this->resourceArticle->setManager(Shopware()->Models());
    }

    // Creates a article with variants
    public function testCreateShouldBeSuccessful()
    {
        // required field name is missing
        $testData = array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',


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
                            array( 'name' => 'Grün')
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
                    'unitId' => 1,

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

                ),
                array(
                    'number' => 'swTEST.variant.' . uniqid(),
                    'inStock' => 17,
                    'unitId' => 1,

                    'attribute' => array(
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ),

                    'configuratorOptions' => array(
                        array(
                            'option' => 'Grün',
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

            'taxId'        => 1,
            'supplierId'   => 2,


        );

        $article = $this->resourceArticle->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());

        $this->assertEquals($article->getName(), $testData['name']);
        $this->assertEquals($article->getDescription(), $testData['description']);

        $this->assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);
        $this->assertEquals($article->getMainDetail()->getAttribute()->getAttr1(), $testData['mainDetail']['attribute']['attr1']);
        $this->assertEquals($article->getMainDetail()->getAttribute()->getAttr2(), $testData['mainDetail']['attribute']['attr2']);


        $this->assertEquals($testData['taxId'], $article->getTax()->getId());

        $this->assertEquals(2, count($article->getMainDetail()->getPrices()));

        return $article;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     * @param $article \Shopware\Models\Article\Article
     * @return $article \Shopware\Models\Article\Article
     */
    public function testGetOneShouldBeSuccessful($article)
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Variant::HYDRATE_OBJECT);
        /** @var $articleDetail \Shopware\Models\Article\Detail */
        foreach ($article->getDetails() as $articleDetail) {
            $articleDetailById = $this->resource->getOne($articleDetail->getId());
            $articleDetailByNumber = $this->resource->getOneByNumber($articleDetail->getNumber());

            $this->assertEquals($articleDetail->getId(), $articleDetailById->getId());
            $this->assertEquals($articleDetail->getId(), $articleDetailByNumber->getId());

        }

        return $article;

    }

    /**
     * @depends testGetOneShouldBeSuccessful
     * @param $article\Shopware\Models\Article\Article
     */
    public function testDeleteShouldBeSuccessful($article)
    {

        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Variant::HYDRATE_OBJECT);

        $deleteByNumber = true;

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        foreach ($article->getDetails() as $articleDetail) {
            $deleteByNumber = !$deleteByNumber;

            if ($deleteByNumber) {
                $result = $this->resource->delete($articleDetail->getId());
            } else {
                $result = $this->resource->deleteByNumber($articleDetail->getNumber());
            }
            $this->assertInstanceOf('\Shopware\Models\Article\Detail', $result);
            $this->assertEquals(null, $result->getId());
        }

        // Delete the whole article at last
        $this->resourceArticle->delete($article->getId());

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
}
