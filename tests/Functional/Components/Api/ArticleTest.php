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

namespace Shopware\Tests\Components\Api;

use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Resource;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ArticleTest extends TestCase
{
    /**
     * @var Article
     */
    protected $resource;

    /**
     * @return Article
     */
    public function createResource()
    {
        return new Article();
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
                        'name' => 'neueOption' . uniqid(rand())
                    )
                )
            ),
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(rand()),
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
                    'number' => 'swTEST.variant.' . uniqid(rand()),
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
        foreach ($article->getMainDetail()->getPrices() as $price) {
            $this->assertGreaterThan(0, $price->getFrom());
        }
        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                $this->assertGreaterThan(0, $price->getFrom());
            }
        }

        return $article->getId();
    }

    /**
     * Test that creating an article with images generates thumbnails
     *
     * @return int Article Id
     */
    public function testCreateWithImageShouldCreateThumbnails()
    {
        $testData = array(
            'name' => 'Test article with images',
            'description' => 'Test description',
            'active' => true,
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
                        'name' => 'neueOption' . uniqid(rand())
                    )
                )
            ),
            'images' => array(
                array(
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg'
                ),
                array(
                    'link' => 'data:image/png;base64,' . require(__DIR__ . '/fixtures/base64image.php')
                ),
                array(
                    'link' => 'file://' . __DIR__ . '/fixtures/variant-image.png'
                )
            ),
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(rand()),
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
                    'number' => 'swTEST.variant.' . uniqid(rand()),
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
                    'images' => array(
                        array(
                            'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg'
                        )
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
        );

        $article = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());
        $this->assertCount(4, $article->getImages());

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($article->getImages() as $image) {
            $this->assertCount(4, $image->getMedia()->getThumbnails());
            foreach ($image->getMedia()->getThumbnails() as $thumbnail) {
                $this->assertTrue($mediaService->has($thumbnail));
            }
        }
        foreach ($article->getMainDetail()->getPrices() as $price) {
            $this->assertGreaterThan(0, $price->getFrom());
        }
        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                $this->assertGreaterThan(0, $price->getFrom());
            }
        }

        return $article->getId();
    }

    /**
     * @depends testCreateWithImageShouldCreateThumbnails
     * @param int $id
     */
    public function testFlipArticleMainVariantShouldBeSuccessful($id)
    {
        $originalArticle = $this->resource->getOne($id);
        $mainVariantNumber = $originalArticle['mainDetailId'];

        $testData = array(
            'mainDetail' => array(
                'number' => $mainVariantNumber,
                'inStock' => 15,
                'unitId' => 1,
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
            'variants' => array(
                array(
                    'number' => $mainVariantNumber,
                    'inStock' => 15,
                    'unitId' => 1,
                    'isMain' => true,
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
                ),
            ),
        );

        $article = $this->resource->update($id, $testData);

        $this->assertEquals($mainVariantNumber, $article->getMainDetail()->getNumber());
    }

    /**
     * Test that updating an Article with images generates thumbnails
     *
     * @depends testCreateWithImageShouldCreateThumbnails
     * @param int $id
     * @return int
     */
    public function testUpdateWithImageShouldCreateThumbnails($id)
    {
        $testData = array(
            'images' => array(
                array(
                    'link' => 'https://cdn.shopware.de/github/readme_screenshot.png'
                )
            ),
        );

        $article = $this->resource->update($id, $testData);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());

        $this->assertCount(5, $article->getImages());
        foreach ($article->getImages() as $image) {
            $this->assertCount(4, $image->getMedia()->getThumbnails());
            foreach ($image->getMedia()->getThumbnails() as $thumbnail) {
                $this->assertTrue($mediaService->has($thumbnail));
            }
        }

        // Cleanup test data
        $this->resource->delete($id);
    }

    /**
     * Tests the thumbnail generation and their proportional sizes
     *
     * @return int
     */
    public function testCreateWithImageShouldCreateThumbnailsWithRightProportions()
    {
        $testData = array(
            'name' => 'Test article with images and right proportions',
            'description' => 'Test description',
            'active' => true,
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
                        'name' => 'neueOption' . uniqid(rand())
                    )
                )
            ),
            'images' => array(
                array(
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg'
                )
            ),
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(rand()),
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
                    'number' => 'swTEST.variant.' . uniqid(rand()),
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
                    'images' => array(
                        array(
                            'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg'
                        )
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
        );

        $article = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Article', $article);
        $this->assertGreaterThan(0, $article->getId());
        $this->assertCount(2, $article->getImages());

        $proportionalSizes = array(
            '200x200',
            '600x600',
            '1280x1280',
            '140x140'
        );

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($article->getImages() as $image) {
            $thumbnails = $image->getMedia()->getThumbnails();
            $this->assertCount(4, $thumbnails);
            $thumbnails = array_values($thumbnails);

            foreach ($thumbnails as $key => $thumbnail) {
                $this->assertTrue($mediaService->has($thumbnail));

                $image = imagecreatefromstring($mediaService->read($thumbnail));
                $width = imagesx($image);
                $height = imagesy($image);

                $this->assertSame($proportionalSizes[$key], $width . 'x' . $height);
            }
        }

        $this->resource->delete($article->getId());
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
                        'name' => 'neueOption' . uniqid(rand())
                    )
                )
            ),
            'mainDetail' => array(
                'number' => 'swConfigSetMainTest' . uniqid(rand()),
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
                    'number' => 'swConfigSetMainTest.variant.' . uniqid(rand()),
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
                    'number' => 'swConfigSetMainTest.variant.' . uniqid(rand()),
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

        $groups = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Group')->findBy(
                array('name' => array('Group1', 'Group2'))
            );

        foreach ($groups as $group) {
            Shopware()->Models()->remove($group);
        }

        $this->resource->delete($article->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful($id)
    {
        $this->resource->setResultMode(Article::HYDRATE_OBJECT);
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
        $this->resource->setResultMode(Article::HYDRATE_OBJECT);
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
        $this->resource->setResultMode(Article::HYDRATE_OBJECT);
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
        } catch (\Exception $e) {
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
            $this->assertTrue(
                in_array(
                    $variant->getNumber(),
                    array('turn', 'turn.1', 'turn.2', 'turn.3'),
                    'Variant number dont match'
                )
            );

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
        } catch (\Exception $e) {
        }
    }

    public function testUpdateToConfiguratorSetPosition()
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (\Exception $e) {
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
                            array('name' => 'S', 'position' => 123),
                            array('name' => 'M', 'position' => 4)
                        )
                    ),
                    array(
                        'name' => 'Farbe',
                        'options' => array(
                            array('name' => 'grün', 'position' => 99),
                            array('name' => 'blau', 'position' => 11)
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
        $this->assertEquals($updated->getName(), 'Turnschuhe', "Article name doesn't match");

        /**@var $variant \Shopware\Models\Article\Detail */
        foreach ($updated->getDetails() as $variant) {
            $this->assertTrue(
                in_array(
                    $variant->getNumber(),
                    array('turn', 'turn.1', 'turn.2', 'turn.3'),
                    'Variant number dont match'
                )
            );

            /**@var $option \Shopware\Models\Article\Configurator\Option */
            foreach ($variant->getConfiguratorOptions() as $option) {
                $this->assertTrue(in_array($option->getName(), array('M', 'S', 'blau', 'grün')));

                switch ($option->getName()) {
                    case 'M':
                        $this->assertEquals(4, $option->getPosition());
                        break;
                    case 'S':
                        $this->assertEquals(123, $option->getPosition());
                        break;
                    case 'blau':
                        $this->assertEquals(11, $option->getPosition());
                        break;
                    case 'grün':
                        $this->assertEquals(99, $option->getPosition());
                        break;

                    default:
                        $this->assertTrue(false, 'There is an unknown variant.');
                }
            }
        }

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (\Exception $e) {
        }
    }

    public function testUpdateToConfiguratorSetType()
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (\Exception $e) {
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
                'type' => 2,
                'groups' => array(
                    array(
                        'name' => 'Größe',
                        'options' => array(
                            array('name' => 'S', 'position' => 123),
                            array('name' => 'M', 'position' => 4)
                        )
                    ),
                    array(
                        'name' => 'Farbe',
                        'options' => array(
                            array('name' => 'grün', 'position' => 99),
                            array('name' => 'blau', 'position' => 11)
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
        $this->assertEquals($updated->getConfiguratorSet()->getType(), 2, "ConfiguratorSet.Type doesn't match");

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     *
     */
    public function testCreateUseConfiguratorId()
    {
        $configurator = $this->getSimpleConfiguratorSet(2, 5);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);
        $variantNumber = 'swVariant' . uniqid(rand());

        $testData = array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 1,
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(rand()),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => array(
                    array('customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400)
                )
            ),
            'variants' => array(
                array(
                    'number' => $variantNumber,
                    'inStock' => 15,
                    'unitId' => 1,
                    'prices' => array(
                        array('customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400)
                    ),
                    'configuratorOptions' => $variantOptions
                )
            ),
            'configuratorSet' => $configurator
        );

        $article = $this->resource->create($testData);

        $this->resource->setResultMode(Article::HYDRATE_ARRAY);
        $data = $this->resource->getOne($article->getId());

        $this->assertCount(2, $data['details'][0]['configuratorOptions']);

        return $variantNumber;
    }

    /**
     * @depends testCreateUseConfiguratorId
     */
    public function testUpdateUseConfiguratorIds($variantNumber)
    {
        $configurator = $this->getSimpleConfiguratorSet(2, 5);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);

        $id = Shopware()->Db()->fetchOne(
            "SELECT articleID FROM s_articles_details WHERE ordernumber = ?",
            array($variantNumber)
        );

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
            Resource::HYDRATE_OBJECT
        );

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(
            array(
                'media.id as mediaId',
                '2 as main'
            )
        )->from('Shopware\Models\Media\Media', 'media')->addOrderBy('media.id', 'ASC')->setFirstResult(
                5
            )->setMaxResults(4);

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

        /**@var $image \Shopware\Models\Article\Image */
        foreach ($article->getImages() as $image) {
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
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = array();
        $newId = null;
        foreach ($article['images'] as $image) {
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
        foreach ($article->getImages() as $image) {
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
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = array();
        $lastMainId = null;

        foreach ($article['images'] as $image) {
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
        foreach ($updateImages['images'] as &$image) {
            if ($image['id'] !== $lastMainId) {
                $image['main'] = 1;
                $newMainId = $image['id'];
                break;
            }
        }
        $article = $this->resource->update($articleId, $updateImages);
        $this->assertCount(4, $article->getImages());

        $hasMain = false;
        /**@var $image \Shopware\Models\Article\Image */
        foreach ($article->getImages() as $image) {
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

        for ($i = 1; $i <= 20; $i++) {
            $definedTranslation[0]['__attribute_attr' . $i] = 'English-Attr' . $i;
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

        for ($i = 1; $i <= 20; $i++) {
            $attr = '__attribute_attr' . $i;
            $this->assertEquals($definedTranslation[$attr], $savedTranslation[$attr]);
        }
    }

    public function testBase64ImageUpload()
    {
        $data = $this->getSimpleTestData();

        $data['images'] = array(
            array(
                'link' => 'data:image/png;base64,' . require(__DIR__ . '/fixtures/base64image.php')
            )
        );

        $model = $this->resource->create($data);
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($model->getId());

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $this->assertCount(count($data['images']), $article['images']);
        foreach ($article['images'] as $image) {
            $key = 'media/image/' . $image['path'] . '.' . $image['extension'];
            $this->assertTrue($mediaService->has($key));

            $imageContent = $mediaService->read($key);

            $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $imageContent);
            $this->assertEquals('image/png', $mimeType);
        }
    }

    public function testImageReplacement()
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $createdIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            array(
                ':articleId' => $article->getId()
            )
        );

        $data = array(
            '__options_images' => array('replace' => true),
            'images' => $this->getImagesForNewArticle(100)
        );

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            array(
                ':articleId' => $article->getId()
            )
        );

        foreach ($updateIds as $id) {
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

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            array(
                ':articleId' => $article->getId()
            )
        );

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

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            array(
                ':articleId' => $article->getId()
            )
        );

        $this->assertCount(10, $updateIds);
    }

    public function testImageAttributes()
    {
        $data = $this->getSimpleTestData();
        $images = $this->getImagesForNewArticle();
        foreach ($images as &$image) {
            $image['attribute'] = array(
                'attribute1' => 'attr1'
            );
        }
        $data['images'] = $images;
        $article = $this->resource->create($data);

        /**@var $image \Shopware\Models\Article\Image */
        foreach ($article->getImages() as $image) {
            $this->assertInstanceOf('\Shopware\Models\Attribute\ArticleImage', $image->getAttribute());
            $this->assertEquals('attr1', $image->getAttribute()->getAttribute1());
            $this->assertEquals(null, $image->getAttribute()->getAttribute2());
            $this->assertEquals(null, $image->getAttribute()->getAttribute3());
        }
    }

    public function testCreateWithDuplicateProperties()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('values', 'option'))->from('Shopware\Models\Property\Value', 'values')->innerJoin(
                'values.option',
                'option'
            )->setFirstResult(0)->setMaxResults(20);
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
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($article->getId());
        foreach ($article['propertyValues'] as $value) {
            $this->assertTrue(in_array($value['id'], $valueIds));
            $this->assertTrue(in_array($value['optionId'], $optionIds));
        }
    }

    public function testCreateWithMultiplePropertiesAndNewGroup()
    {
        $data = $this->getSimpleTestData();

        $optionName = 'newOption' . uniqid(rand());
        $properties = array(
            array(
                'option' => array('name' => $optionName),
                'value' => 'testValue'
            ),
            array(
                'option' => array('name' => $optionName),
                'value' => 'anotherTestValue'
            )
        );

        $data['propertyValues'] = $properties;
        $data['filterGroupId'] = 1;
        $article = $this->resource->create($data);
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $articleId = $article->getId();
        $article = $this->resource->getOne($articleId);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('option'))->from('Shopware\Models\Property\Option', 'option')->where(
                'option.name = :optionName'
            )->setParameter('optionName', $optionName)->setFirstResult(0)->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        $this->assertEquals($article['propertyValues'][0]['optionId'], $article['propertyValues'][1]['optionId']);
        $this->assertEquals(1, count($databaseValuesOptions));

        $this->resource->delete($articleId);

        //delete test values in s_filter_values
        $sql = "DELETE FROM `s_filter_values` WHERE `optionId` = ?";
        Shopware()->Db()->query($sql, array($databaseValuesOptions[0]['id']));

        //delete test values in s_filter_relations
        $sql = "DELETE FROM `s_filter_relations` WHERE `optionId` = ?";
        Shopware()->Db()->query($sql, array($databaseValuesOptions[0]['id']));

        //delete test values in s_filter_options
        $builder->delete('Shopware\Models\Property\Option', 'option')->andWhere(
                'option.name = :optionName'
            )->setParameter('optionName', $optionName)->getQuery()->execute();
    }

    public function testUpdateWithDuplicateProperties()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('values', 'option'))->from('Shopware\Models\Property\Value', 'values')->innerJoin(
                'values.option',
                'option'
            )->setFirstResult(0)->setMaxResults(20);
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
            Resource::HYDRATE_OBJECT
        );
        $article = $this->resource->create($data);
        /**@var \$article Shopware\Models\Article\Article */
        $article = $this->resource->update($article->getId(), $update);
        /**@var $value \Shopware\Models\Property\Value */
        foreach ($article->getPropertyValues() as $value) {
            $this->assertTrue(in_array($value->getId(), $valueIds));
            $this->assertTrue(in_array($value->getOption()->getId(), $optionIds));
        }
    }

    public function testPriceReplacement()
    {
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

    public function testUpdateWithMultiplePropertiesAndNewGroup()
    {
        $optionName = 'newOption' . uniqid(rand());
        $properties = array(
            array(
                'option' => array('name' => $optionName),
                'value' => 'testValue'
            ),
            array(
                'option' => array('name' => $optionName),
                'value' => 'anotherTestValue'
            )
        );

        $update = array(
            'propertyValues' => $properties,
            'filterGroupId' => 1
        );
        $data = $this->getSimpleTestData();
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );
        $article = $this->resource->create($data);
        /**@var $article \Shopware\Models\Article\Article */
        $article = $this->resource->update($article->getId(), $update);

        $articleId = $article->getId();
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($article->getId());

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('option'))->from('Shopware\Models\Property\Option', 'option')->where(
                'option.name = :optionName'
            )->setParameter('optionName', $optionName)->setFirstResult(0)->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        $this->assertEquals($article['propertyValues'][0]['optionId'], $article['propertyValues'][1]['optionId']);
        $this->assertEquals(1, count($databaseValuesOptions));

        $this->resource->delete($articleId);

        //delete test values in s_filter_values
        $sql = "DELETE FROM `s_filter_values` WHERE `optionId` = ?";
        Shopware()->Db()->query($sql, array($databaseValuesOptions[0]['id']));

        //delete test values in s_filter_relations
        $sql = "DELETE FROM `s_filter_relations` WHERE `optionId` = ?";
        Shopware()->Db()->query($sql, array($databaseValuesOptions[0]['id']));

        //delete test values in s_filter_options
        $builder->delete('Shopware\Models\Property\Option', 'option')->andWhere(
                'option.name = :optionName'
            )->setParameter('optionName', $optionName)->getQuery()->execute();
    }

    public function testImageConfiguration()
    {
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
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
        foreach ($images as &$image) {
            $image['options'] = array($usedOption);
        }

        $create['images'] = $images;
        $create['configuratorSet'] = $configurator;
        $create['variants'] = $variants;

        $article = $this->resource->create($create);

        /**@var $image \Shopware\Models\Article\Image */
        foreach ($article->getImages() as $image) {
            $this->assertCount(1, $image->getMappings());

            /**@var $mapping \Shopware\Models\Article\Image\Mapping */
            foreach ($image->getMappings() as $mapping) {
                $this->assertCount(1, $mapping->getRules());
            }
        }

        $this->resource->generateVariantImages($article->getId());

        $article = $this->resource->getOne($article->getId());

        /**@var $variant \Shopware\Models\Article\Detail */
        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getConfiguratorOptions() as $option) {
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
        foreach ($configuratorSet['groups'] as $group) {
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
    ) {
        $options = array();

        $groupArrayKey = $groupMapping['key'];
        $groupValuesKey = $groupMapping['value'];
        $optionArrayKey = $optionMapping['key'];
        $optionValuesKey = $optionMapping['value'];

        foreach ($groups as $group) {
            $groupOptions = array();
            foreach ($group['options'] as $option) {
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
        foreach ($combinations as $combination) {
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
    protected function cleanUpCombinations($combinations)
    {
        foreach ($combinations as &$combination) {
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
    protected function combinations($arrays, $i = 0)
    {
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
                $result[] = is_array($t) ? array_merge(array($v), $t) : array($v, $t);
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
        $builder->select(
            array(
                'media.id as mediaId',
                '2 as main'
            )
        )->from('Shopware\Models\Media\Media', 'media', 'media.id')->addOrderBy('media.id', 'ASC')->setFirstResult(
                $offset
            )->setMaxResults($limit);

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

        /**@var $price \Shopware\Models\Article\Price */
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
        foreach ($articles as $article) {
            $model = Shopware()->Models()->find(
                'Shopware\Models\Article\Article',
                $article['id']
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
        foreach ($articles as $article) {
            $model = Shopware()->Models()->find(
                'Shopware\Models\Article\Article',
                $article['id']
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
            array('link' => 'data:image/png;base64,' . require(__DIR__ . '/fixtures/base64image.php'))
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

    public function testSeoCategories()
    {
        $data = $this->getSimpleTestData();

        $data['categories'] = Shopware()->Db()->fetchAll("SELECT DISTINCT id FROM s_categories LIMIT 5, 10");

        $first = $data['categories'][3];
        $second = $data['categories'][4];

        $ids = array($first['id'], $second['id']);

        $data['seoCategories'] = array(
            array('shopId' => 1, 'categoryId' => $first['id']),
            array('shopId' => 2, 'categoryId' => $second['id']),
        );

        $article = $this->resource->create($data);

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        /**@var $article \Shopware\Models\Article\Article */
        $article = $this->resource->getOne($article->getId());

        $this->assertCount(2, $article->getSeoCategories());

        foreach ($article->getSeoCategories() as $category) {
            $this->assertContains($category->getCategory()->getId(), $ids);
            $this->assertContains($category->getShop()->getId(), array(1, 2));
        }
    }

    public function testArticleGrossPrices()
    {
        $data = $this->getSimpleTestData();

        $article = $this->resource->create($data);

        $this->assertInstanceOf('Shopware\Models\Article\Article', $article);

        /**@var $price \Shopware\Models\Article\Price */
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

    private function getSimpleTestData()
    {
        return array(
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(rand()),
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
            foreach ($fields as $field) {
                $selectFields[] = 'alias.' . $field;
            }
        } else {
            $selectFields = array('alias');
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($selectFields)->from($entity, 'alias')->setFirstResult($offset)->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    private function getSimpleConfiguratorSet($groupLimit = 3, $optionLimit = 5)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('groups.id', 'groups.name'))->from(
                'Shopware\Models\Article\Configurator\Group',
                'groups'
            )->setFirstResult(0)->setMaxResults($groupLimit)->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('options.id', 'options.name'))->from(
                'Shopware\Models\Article\Configurator\Option',
                'options'
            )->where('options.groupId = :groupId')->setFirstResult(0)->setMaxResults($optionLimit)->orderBy(
                'options.position',
                'ASC'
            );

        foreach ($groups as &$group) {
            $builder->setParameter('groupId', $group['id']);
            $group['options'] = $builder->getQuery()->getArrayResult();
        }

        return array(
            'name' => 'Test-Set',
            'groups' => $groups
        );
    }

    private function getSimpleVariantData()
    {
        return array(
            'number' => 'swTEST' . uniqid(rand()),
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
        } catch (\Exception $e) {
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
            function ($category) {
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

        $result = $this->resource->batch(
            array(
                'new' => $createNew,
                'existingByNumber' => $updateExistingByNumber,
                'existingById' => $updateExistingById,
            )
        );

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
        foreach ($configuratorSet['groups'] as $group) {
            $id = rand(0, count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = array(
                'optionId' => $option['id'],
                'groupId' => $group['id']
            );
        }
        return $options;
    }

    public function testCategoryAssignment()
    {
        $number = 'CategoryAssignment' . uniqid(rand());

        $data = $this->getSimpleTestData();
        $data['mainDetail']['number'] = $number;

        $categories = Shopware()->Db()->fetchAll("SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2");
        $data['categories'] = $categories;

        $article = $this->resource->create($data);

        $normal = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories WHERE articleID = ?",
            array($article->getId())
        );

        $denormalized = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?",
            array($article->getId())
        );

        $this->assertCount(2, $normal);
        $this->assertCount(4, $denormalized);

        foreach ($categories as $category) {
            $this->assertContains($category['id'], $normal);
            $this->assertContains($category['id'], $denormalized);
        }

        $rewriteCategories = Shopware()->Db()->fetchAll(
            "SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2, 2"
        );
        $data = array(
            'categories' => $rewriteCategories
        );

        $this->resource->update($article->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories WHERE articleID = ?",
            array($article->getId())
        );

        $denormalized = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?",
            array($article->getId())
        );

        $this->assertCount(2, $normal);
        $this->assertCount(4, $denormalized);

        foreach ($rewriteCategories as $category) {
            $this->assertContains($category['id'], $normal);
            $this->assertContains(
                $category['id'],
                $denormalized,
                "Denormalized array contains not the expected category id"
            );
        }

        $additionally = Shopware()->Db()->fetchAll("SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2");
        $data = array(
            '__options_categories' => array('replace' => false),
            'categories' => $additionally
        );
        $this->resource->update($article->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories WHERE articleID = ?",
            array($article->getId())
        );

        $denormalized = Shopware()->Db()->fetchCol(
            "SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?",
            array($article->getId())
        );

        $this->assertCount(4, $normal);
        $this->assertCount(8, $denormalized);

        foreach ($rewriteCategories as $category) {
            $this->assertContains($category['id'], $normal);
            $this->assertContains(
                $category['id'],
                $denormalized,
                "Denormalized array contains not the expected category id"
            );
        }

        foreach ($additionally as $category) {
            $this->assertContains($category['id'], $normal);
            $this->assertContains(
                $category['id'],
                $denormalized,
                "Denormalized array contains not the expected category id"
            );
        }
    }
}
