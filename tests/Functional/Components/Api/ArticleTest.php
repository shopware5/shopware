<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Api;

use Doctrine\Common\Collections\Criteria;
use Exception;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\ArticleImage;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media;
use Shopware\Models\Property\Value;

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

    public function testCreateShouldBeSuccessful(): int
    {
        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'metaTitle' => 'this is a test title with umlauts äöüß',
            'filterGroupId' => 1,
            'propertyValues' => [
                [
                    'value' => 'grün',
                    'option' => [
                        'name' => 'Farbe',
                    ],
                ],
                [
                    'value' => 'testWert',
                    'option' => [
                        'name' => 'neueOption' . uniqid((string) rand()),
                    ],
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'attribute' => [
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ],
                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'to' => 20,
                        'price' => 500,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'MeinKonf',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'grün'],
                        ],
                    ],
                    [
                        'name' => 'Gräße',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
            'similar' => [
                ['id' => 5],
                ['id' => 6],
            ],
            'categories' => [
                ['id' => 15],
                ['id' => 10],
            ],
            'related' => [
                ['id' => 3, 'cross' => true],
                ['id' => 4],
            ],
            'links' => [
                ['name' => 'foobar', 'link' => 'http://example.org'],
                ['name' => 'Video', 'link' => 'http://example.org'],
            ],
        ];

        $article = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());

        static::assertEquals($article->getName(), $testData['name']);
        static::assertEquals($article->getDescription(), $testData['description']);
        static::assertEquals($article->getMetaTitle(), $testData['metaTitle']);

        static::assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);

        // Check attributes of main variant
        static::assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        static::assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );

        // Check attributes of non-main variant
        $variant = $article->getDetails()->matching(Criteria::create()->where(
            Criteria::expr()->eq('number', $testData['variants'][0]['number'])
        ));
        static::assertEquals(
            $variant->first()->getAttribute()->getAttr3(),
            $testData['variants'][0]['attribute']['attr3']
        );
        static::assertEquals(
            $variant->first()->getAttribute()->getAttr4(),
            $testData['variants'][0]['attribute']['attr4']
        );

        $propertyValues = $article->getPropertyValues()->getValues();
        static::assertCount(\count($testData['propertyValues']), $propertyValues);
        foreach ($propertyValues as $propertyValue) {
            static::assertContains($propertyValue->getValue(), ['grün', 'testWert']);
        }

        static::assertEquals($testData['taxId'], $article->getTax()->getId());

        static::assertCount(2, $article->getCategories());
        static::assertCount(2, $article->getRelated());
        static::assertCount(2, $article->getSimilar());
        static::assertCount(2, $article->getLinks());
        static::assertCount(2, $article->getMainDetail()->getPrices());
        foreach ($article->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }
        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                static::assertGreaterThan(0, $price->getFrom());
            }
        }

        return $article->getId();
    }

    public function testCreateWithNewUnitShouldBeSuccessful(): int
    {
        $testData = [
            'name' => 'Testarticle',
            'description' => 'testdescription',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'tax' => 19,
            'categories' => [
                ['id' => 15],
                ['id' => 10],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                // create new unit
                'unit' => [
                    'name' => 'newunit',
                    'unit' => 'newunit',
                ],
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'price' => 999,
                    ],
                ],
            ],
        ];

        $article = $this->resource->create($testData);
        // change number for second article
        $testData['mainDetail']['number'] = 'swTEST' . uniqid((string) rand());
        $secondArticle = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());

        static::assertEquals($article->getName(), $testData['name']);
        static::assertEquals($article->getDescription(), $testData['description']);
        static::assertEquals($article->getMetaTitle(), $testData['metaTitle']);

        foreach ($article->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }

        static::assertInstanceOf(Unit::class, $article->getMainDetail()->getUnit());
        static::assertGreaterThan(0, $article->getMainDetail()->getUnit()->getId());
        static::assertEquals($article->getMainDetail()->getUnit()->getName(), $testData['mainDetail']['unit']['name']);
        static::assertEquals($article->getMainDetail()->getUnit()->getUnit(), $testData['mainDetail']['unit']['unit']);

        static::assertEquals($article->getMainDetail()->getUnit()->getId(), $secondArticle->getMainDetail()->getUnit()->getId());

        return $article->getId();
    }

    /*
     * Test that empty article attributes are created
     */
    public function testCreateWithoutAttributes(): void
    {
        $configurator = $this->getSimpleConfiguratorSet(2);

        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 1,
            'mainDetail' => [
                'number' => 'swAttr1' . uniqid((string) rand(), true),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    ['customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400],
                ],
                'configuratorOptions' => $this->getVariantOptionsOfSet($configurator),
            ],
            'variants' => [
                [
                    'number' => 'swAttr2' . uniqid((string) rand(), true),
                    'inStock' => 15,
                    'unitId' => 1,
                    'prices' => [
                        ['customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400],
                    ],
                    'configuratorOptions' => $this->getVariantOptionsOfSet($configurator),
                ],
            ],
            'configuratorSet' => $configurator,
        ];

        $article = $this->resource->create($testData);

        // Load actual database model
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $data = $this->resource->getOne($article->getId());

        static::assertEquals(2, $data->getDetails()->count());
        foreach ($data->getDetails() as $variant) {
            static::assertNotNull($variant->getAttribute());
            static::assertNull($variant->getAttribute()->getAttr1());
        }
    }

    /**
     * Test that creating an article with images generates thumbnails
     *
     * @return int Article Id
     */
    public function testCreateWithImageShouldCreateThumbnails(): int
    {
        $testData = [
            'name' => 'Test article with images',
            'description' => 'Test description',
            'active' => true,
            'filterGroupId' => 1,
            'propertyValues' => [
                [
                    'value' => 'grün',
                    'option' => [
                        'name' => 'Farbe',
                    ],
                ],
                [
                    'value' => 'testWert',
                    'option' => [
                        'name' => 'neueOption' . uniqid((string) rand()),
                    ],
                ],
            ],
            'images' => [
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                ],
                [
                    'link' => 'data:image/png;base64,' . require (__DIR__ . '/fixtures/base64image.php'),
                ],
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/variant-image.png',
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'attribute' => [
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ],
                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'to' => 20,
                        'price' => 500,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'MeinKonf',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'grün'],
                        ],
                    ],
                    [
                        'name' => 'Gräße',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'images' => [
                        [
                            'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                        ],
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
        ];

        $article = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());
        static::assertCount(4, $article->getImages());

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($article->getImages() as $image) {
            static::assertCount(4, $image->getMedia()->getThumbnails());
            foreach ($image->getMedia()->getThumbnails() as $thumbnail) {
                static::assertTrue($mediaService->has($thumbnail));
            }
        }
        foreach ($article->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }
        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                static::assertGreaterThan(0, $price->getFrom());
            }
        }

        return $article->getId();
    }

    /**
     * @depends testCreateWithImageShouldCreateThumbnails
     */
    public function testFlipArticleMainVariantShouldBeSuccessful(int $id): void
    {
        $originalArticle = $this->resource->getOne($id);
        $mainVariantNumber = (string) $originalArticle['mainDetailId'];

        $testData = [
            'mainDetail' => [
                'number' => $mainVariantNumber,
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => $mainVariantNumber,
                    'inStock' => 15,
                    'unitId' => 1,
                    'isMain' => true,
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'EK',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],
                ],
            ],
        ];

        $article = $this->resource->update($id, $testData);

        static::assertEquals($mainVariantNumber, $article->getMainDetail()->getNumber());
    }

    /**
     * Test that updating an Article with images generates thumbnails
     *
     * @depends testCreateWithImageShouldCreateThumbnails
     */
    public function testUpdateWithImageShouldCreateThumbnails(int $id): void
    {
        $testData = [
            'images' => [
                [
                    'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
                ],
            ],
        ];

        $article = $this->resource->update($id, $testData);
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());

        static::assertCount(5, $article->getImages());
        foreach ($article->getImages() as $image) {
            static::assertCount(4, $image->getMedia()->getThumbnails());
            foreach ($image->getMedia()->getThumbnails() as $thumbnail) {
                static::assertTrue($mediaService->has($thumbnail));
            }
        }

        // Cleanup test data
        $this->resource->delete($id);
    }

    /**
     * Tests the thumbnail generation and their proportional sizes
     */
    public function testCreateWithImageShouldCreateThumbnailsWithRightProportions(): void
    {
        $testData = [
            'name' => 'Test article with images and right proportions',
            'description' => 'Test description',
            'active' => true,
            'filterGroupId' => 1,
            'propertyValues' => [
                [
                    'value' => 'grün',
                    'option' => [
                        'name' => 'Farbe',
                    ],
                ],
                [
                    'value' => 'testWert',
                    'option' => [
                        'name' => 'neueOption' . uniqid((string) rand()),
                    ],
                ],
            ],
            'images' => [
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'attribute' => [
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ],
                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'MeinKonf',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'grün'],
                        ],
                    ],
                    [
                        'name' => 'Gräße',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'images' => [
                        [
                            'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                        ],
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
        ];

        $article = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());
        static::assertCount(2, $article->getImages());

        $proportionalSizes = [
            '200x200',
            '600x600',
            '1280x1280',
            '140x140',
        ];

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($article->getImages() as $image) {
            $thumbnails = $image->getMedia()->getThumbnails();
            static::assertCount(4, $thumbnails);
            $thumbnails = array_values($thumbnails);

            foreach ($thumbnails as $key => $thumbnail) {
                static::assertTrue($mediaService->has($thumbnail));

                $image = imagecreatefromstring($mediaService->read($thumbnail));
                $width = imagesx($image);
                $height = imagesy($image);

                static::assertSame($proportionalSizes[$key], $width . 'x' . $height);
            }
        }

        $this->resource->delete($article->getId());
    }

    /**
     * Test creating an article with new configurator set and multiple variants SW-7925
     */
    public function testCreateWithVariantsAndNewConfiguratorSetShouldBeSuccessful(): void
    {
        $testData = [
            'name' => 'Test article',
            'description' => 'Test description',
            'descriptionLong' => 'Long test description',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'metaTitle' => 'this is a test title with umlauts äöüß',
            'filterGroupId' => 1,
            'propertyValues' => [
                [
                    'value' => 'grün',
                    'option' => [
                        'name' => 'Farbe',
                    ],
                ],
                [
                    'value' => 'testWert',
                    'option' => [
                        'name' => 'neueOption' . uniqid((string) rand()),
                    ],
                ],
            ],
            'mainDetail' => [
                'number' => 'swConfigSetMainTest' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'attribute' => [
                    'attr1' => 'Freitext1',
                    'attr2' => 'Freitext2',
                ],
                'minPurchase' => 5,
                'purchaseSteps' => 2,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ],
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 21,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'NewConfigSet',
                'groups' => [
                    [
                        'name' => 'Group1',
                        'options' => [
                            ['name' => 'Opt11'],
                            ['name' => 'Opt12'],
                        ],
                    ],
                    [
                        'name' => 'Group2',
                        'options' => [
                            ['name' => 'Opt21'],
                            ['name' => 'Opt22'],
                            ['name' => 'Opt23'],
                            ['name' => 'Opt24'],
                            ['name' => 'Opt25'],
                            ['name' => 'Opt26'],
                            ['name' => 'Opt27'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swConfigSetMainTest.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Opt11',
                            'group' => 'Group1',
                        ],
                        [
                            'option' => 'Opt23',
                            'group' => 'Group2',
                        ],
                        [
                            'option' => 'Opt24',
                            'group' => 'Group2',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
                [
                    'number' => 'swConfigSetMainTest.variant.' . uniqid((string) rand()),
                    'inStock' => 18,
                    // create another new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Opt12',
                            'group' => 'Group1',
                        ],
                        [
                            'option' => 'Opt27',
                            'group' => 'Group2',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'from' => 1,
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
            'categories' => [
                ['id' => 15],
                ['id' => 10],
            ],
            'links' => [
                ['name' => 'foobar', 'link' => 'http://example.org'],
                ['name' => 'Video', 'link' => 'http://example.org'],
            ],
        ];

        $article = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());

        static::assertEquals($article->getName(), $testData['name']);
        static::assertEquals($article->getDescription(), $testData['description']);
        static::assertEquals($article->getMetaTitle(), $testData['metaTitle']);

        static::assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);
        static::assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        static::assertEquals(
            $article->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );

        $propertyValues = $article->getPropertyValues()->getValues();
        static::assertCount(\count($testData['propertyValues']), $propertyValues);
        foreach ($propertyValues as $propertyValue) {
            static::assertContains($propertyValue->getValue(), ['grün', 'testWert']);
        }

        static::assertEquals($testData['taxId'], $article->getTax()->getId());

        static::assertCount(2, $article->getCategories());
        static::assertCount(0, $article->getRelated());
        static::assertCount(0, $article->getSimilar());
        static::assertCount(2, $article->getLinks());
        static::assertCount(2, $article->getMainDetail()->getPrices());

        $groups = Shopware()->Models()->getRepository(ConfiguratorGroup::class)->findBy(
            ['name' => ['Group1', 'Group2']]
        );

        foreach ($groups as $group) {
            Shopware()->Models()->remove($group);
        }

        $this->resource->delete($article->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneByNumberShouldBeSuccessful($id): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);
        $number = $article->getMainDetail()->getNumber();

        $article = $this->resource->getOneByNumber($number);
        static::assertEquals($id, $article->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id): void
    {
        $article = $this->resource->getOne($id);
        static::assertGreaterThan(0, $article['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful(): void
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * Tests that getList uses only the main variants attributes for filtering
     *
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldUseCorrectDetailsAttribute(int $id): void
    {
        // Filter with attribute of main variant => article found
        $result = $this->resource->getList(0, 1, [
            'id' => $id,
            'attribute.attr1' => 'Freitext1', // Belongs to main variant
        ]);

        static::assertEquals(1, $result['total']);
        static::assertEquals($id, $result['data'][0]['id'], (string) $id);

        // Filter with attribute of other (non-main) variant => no result
        $result = $this->resource->getList(0, 1, [
            'id' => $id,
            'attribute.attr3' => 'Freitext3',
        ]);

        static::assertEquals(0, $result['total']);
    }

    public function testCreateWithInvalidDataShouldThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        // required field name is missing
        $testData = [
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        ];

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateByNumberShouldBeSuccessful($id): ?string
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $article = $this->resource->getOne($id);
        $number = $article->getMainDetail()->getNumber();

        $testData = [
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
            // update supplier id
            'supplierId' => 3,
            // categories should be replaced
            'categories' => [
                ['id' => 16],
            ],
            'filterGroupId' => 1,
            // values should be replaced
            'propertyValues' => [],
            // related is not included, therefore it stays untouched

            // similar is set to empty array, therefore it should be cleared
            'similar' => [],
        ];

        $article = $this->resource->updateByNumber($number, $testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertEquals($id, $article->getId());
        static::assertEquals($article->getDescription(), $testData['description']);
        static::assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);

        static::assertEquals($testData['supplierId'], $article->getSupplier()->getId());

        $propertyValues = $article->getPropertyValues()->getValues();
        static::assertCount(\count($propertyValues), $testData['propertyValues']);

        // Categories should be updated
        static::assertCount(1, $article->getCategories());

        // Related should be untouched
        static::assertCount(2, $article->getRelated());

        // Similar should be removed
        static::assertCount(0, $article->getSimilar());

        return $number;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id): int
    {
        $testData = [
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
            // update supplier id
            'supplierId' => 3,
            // categories should be replaced
            'categories' => [
                ['id' => 16],
            ],
            'filterGroupId' => 1,
            // values should be replaced
            'propertyValues' => [],
            // related is not included, therefore it stays untouched

            // similar is set to empty array, therefore it should be cleared
            'similar' => [],
        ];

        $article = $this->resource->update($id, $testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertEquals($id, $article->getId());
        static::assertEquals($article->getDescription(), $testData['description']);
        static::assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);

        static::assertEquals($testData['supplierId'], $article->getSupplier()->getId());

        $propertyValues = $article->getPropertyValues()->getValues();
        static::assertCount(\count($propertyValues), $testData['propertyValues']);

        // Categories should be updated
        static::assertCount(1, $article->getCategories());

        // Related should be untouched
        static::assertCount(2, $article->getRelated());

        // Similar should be removed
        static::assertCount(0, $article->getSimilar());

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id): void
    {
        $this->expectException(ValidationException::class);
        // required field name is blank
        $testData = [
            'name' => ' ',
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        ];

        $this->resource->update($id, $testData);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id): void
    {
        $article = $this->resource->delete($id);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertSame(0, (int) $article->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete('');
    }

    /**
     * Test case to add a new article image over a media id.
     */
    public function testAddArticleMediaOverMediaId(): void
    {
        $this->resource->update(
            2,
            [
                'images' => [
                    [
                        'articleId' => 2,
                        'mediaId' => 25,
                        'main' => 0,
                        'position' => 10000,
                    ],
                ],
            ]
        );
        $article = $this->resource->getOne(2);

        $image = array_pop($article['images']);
        static::assertEquals(25, $image['mediaId']);
    }

    public function testUpdateToVariantArticle(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'groups' => [
                    [
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'S'],
                            ['name' => 'M'],
                        ],
                    ],
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'grün'],
                            ['name' => 'blau'],
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'variants' => [
                [
                    'isMain' => true,
                    'number' => 'turn',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 1999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.1',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.2',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.3',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
            ],
        ];
        $updated = $this->resource->update($article->getId(), $updateArticle);
        static::assertEquals('Turnschuhe', $updated->getName(), "Article name don't match");

        foreach ($updated->getDetails() as $variant) {
            static::assertContains(
                $variant->getNumber(),
                ['turn', 'turn.1', 'turn.2', 'turn.3'],
                'Variant number dont match'
            );

            static::assertCount(2, $variant->getConfiguratorOptions(), 'Configurator option count dont match');

            foreach ($variant->getConfiguratorOptions() as $option) {
                static::assertTrue(\in_array($option->getName(), ['M', 'S', 'blau', 'grün']));
            }
        }

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    public function testUpdateToConfiguratorSetPosition(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'groups' => [
                    [
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'S', 'position' => 123],
                            ['name' => 'M', 'position' => 4],
                        ],
                    ],
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'grün', 'position' => 99],
                            ['name' => 'blau', 'position' => 11],
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'variants' => [
                [
                    'isMain' => true,
                    'number' => 'turn',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 1999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.1',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.2',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.3',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
            ],
        ];
        $updated = $this->resource->update($article->getId(), $updateArticle);
        static::assertEquals('Turnschuhe', $updated->getName(), "Article name doesn't match");

        foreach ($updated->getDetails() as $variant) {
            static::assertContains(
                $variant->getNumber(),
                ['turn', 'turn.1', 'turn.2', 'turn.3'],
                'Variant number dont match'
            );

            foreach ($variant->getConfiguratorOptions() as $option) {
                static::assertContains($option->getName(), ['M', 'S', 'blau', 'grün']);

                switch ($option->getName()) {
                    case 'M':
                        static::assertEquals(4, $option->getPosition());
                        break;
                    case 'S':
                        static::assertEquals(123, $option->getPosition());
                        break;
                    case 'blau':
                        static::assertEquals(11, $option->getPosition());
                        break;
                    case 'grün':
                        static::assertEquals(99, $option->getPosition());
                        break;

                    default:
                        static::fail('There is an unknown variant.');
                }
            }
        }

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    public function testUpdateToConfiguratorSetType(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'type' => 2,
                'groups' => [
                    [
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'S', 'position' => 123],
                            ['name' => 'M', 'position' => 4],
                        ],
                    ],
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'grün', 'position' => 99],
                            ['name' => 'blau', 'position' => 11],
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'variants' => [
                [
                    'isMain' => true,
                    'number' => 'turn',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 1999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.1',
                    'inStock' => 15,
                    'addtionnaltext' => 'S / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'S'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.2',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / grün',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'grün'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
                [
                    'isMain' => false,
                    'number' => 'turn.3',
                    'inStock' => 15,
                    'addtionnaltext' => 'M / blau',
                    'configuratorOptions' => [
                        ['group' => 'Größe', 'option' => 'M'],
                        ['group' => 'Farbe', 'option' => 'blau'],
                    ],
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
            ],
        ];
        $updated = $this->resource->update($article->getId(), $updateArticle);
        static::assertEquals(2, $updated->getConfiguratorSet()->getType(), "ConfiguratorSet.Type doesn't match");

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    public function testUpdateToConfiguratorSetPositionsShouldBeGenerated(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'type' => 2,
                'groups' => [
                    [
                        'name' => 'Foo Farbe',
                        'options' => [
                            ['name' => 'Rot'],
                            ['name' => 'Blau'],
                            ['name' => 'Weiß'],
                        ],
                    ],
                ],
            ],
        ];
        $options = $this->resource->update($article->getId(), $updateArticle)->getConfiguratorSet()->getOptions();

        static::assertSame(0, $options[0]->getPosition());
        static::assertSame(1, $options[1]->getPosition());
        static::assertSame(2, $options[2]->getPosition());

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @depends testUpdateToConfiguratorSetPositionsShouldBeGenerated
     */
    public function testUpdateToConfiguratorSetPositionsShouldOverwritePositions(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'type' => 2,
                'groups' => [
                    [
                        'name' => 'Foo Farbe',
                        'options' => [
                            ['name' => 'Rot', 'position' => 5],
                            ['name' => 'Blau', 'position' => 6],
                            ['name' => 'Weiß', 'position' => 11],
                        ],
                    ],
                ],
            ],
        ];
        $options = $this->resource->update($article->getId(), $updateArticle)->getConfiguratorSet()->getOptions();

        static::assertSame(5, $options[0]->getPosition());
        static::assertSame(6, $options[1]->getPosition());
        static::assertSame(11, $options[2]->getPosition());

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @depends testUpdateToConfiguratorSetPositionsShouldBeGenerated
     * @depends testUpdateToConfiguratorSetPositionsShouldOverwritePositions
     */
    public function testUpdateToConfiguratorSetPositionsShouldRemainUntouched(): void
    {
        try {
            $id = $this->resource->getIdFromNumber('turn');
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }

        $article = $this->createConfiguratorSetProduct();

        $updateArticle = [
            'configuratorSet' => [
                'type' => 2,
                'groups' => [
                    [
                        'name' => 'Foo Farbe',
                        'options' => [
                            ['name' => 'Rot'],
                            ['name' => 'Weiß'],
                        ],
                    ],
                ],
            ],
        ];
        $options = $this->resource->update($article->getId(), $updateArticle)->getConfiguratorSet()->getOptions();

        static::assertSame(5, $options[0]->getPosition());
        static::assertSame(11, $options[1]->getPosition());

        try {
            if (!empty($id)) {
                $this->resource->delete($id);
            }
        } catch (Exception $e) {
        }
    }

    public function testCreateUseConfiguratorId(): string
    {
        $configurator = $this->getSimpleConfiguratorSet(2);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);
        $variantNumber = 'swVariant' . uniqid((string) rand());

        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 1,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    ['customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400],
                ],
            ],
            'variants' => [
                [
                    'number' => $variantNumber,
                    'inStock' => 15,
                    'unitId' => 1,
                    'prices' => [
                        ['customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400],
                    ],
                    'configuratorOptions' => $variantOptions,
                ],
            ],
            'configuratorSet' => $configurator,
        ];

        $article = $this->resource->create($testData);

        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $data = $this->resource->getOne($article->getId());

        static::assertCount(2, $data['details'][0]['configuratorOptions']);

        return $variantNumber;
    }

    /**
     * @depends testCreateUseConfiguratorId
     */
    public function testUpdateUseConfiguratorIds($variantNumber): void
    {
        $configurator = $this->getSimpleConfiguratorSet(2);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);

        $id = Shopware()->Db()->fetchOne(
            'SELECT articleID FROM s_articles_details WHERE ordernumber = ?',
            [$variantNumber]
        );

        $data = [
            'variants' => [
                [
                    'number' => $variantNumber,
                    'configuratorOptions' => $variantOptions,
                ],
            ],
        ];

        $this->resource->update($id, $data);

        $data = $this->resource->getOne($id);
        static::assertCount(2, $data['details'][0]['configuratorOptions']);
    }

    public function testCreateWithMainImages(): int
    {
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(
            [
                'media.id as mediaId',
                '2 as main',
            ]
        )->from(Media::class, 'media')->addOrderBy('media.id', 'ASC')->setFirstResult(
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

        static::assertCount(4, $article->getImages());

        $mainFlagExists = false;

        foreach ($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $mainFlagExists = true;
                static::assertEquals($expectedMainId, $image->getMedia()->getId());
            }
        }
        static::assertTrue($mainFlagExists);

        return $article->getId();
    }

    /**
     * @depends testCreateWithMainImages
     */
    public function testUpdateWithSingleMainImage($articleId): int
    {
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = [];
        $newId = null;
        foreach ($article['images'] as $image) {
            if ($image['main'] !== 1) {
                $updateImages['images'][] = [
                    'id' => $image['id'],
                    'main' => 1,
                ];
                $newId = $image['id'];
                break;
            }
        }
        $article = $this->resource->update($articleId, $updateImages);

        static::assertCount(4, $article->getImages());

        $hasMain = false;
        foreach ($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                static::assertEquals($image->getId(), $newId);
            }
        }
        static::assertTrue($hasMain);

        return $article->getId();
    }

    /**
     * @depends testUpdateWithSingleMainImage
     */
    public function testUpdateWithMainImage($articleId): void
    {
        $this->resource->getManager()->clear();

        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($articleId);

        $updateImages = [];
        $lastMainId = null;

        foreach ($article['images'] as $image) {
            $newImageData = [
                'id' => $image['id'],
                'main' => $image['main'],
            ];

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
        unset($image);
        $article = $this->resource->update($articleId, $updateImages);
        static::assertCount(4, $article->getImages());

        $hasMain = false;
        foreach ($article->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                static::assertEquals($newMainId, $image->getId());
            }
        }
        static::assertTrue($hasMain);
    }

    /**
     * This unit test, tests if the attribute fields are translatable.
     */
    public function testCreateTranslation(): void
    {
        $crud = Shopware()->Container()->get(CrudServiceInterface::class);

        $crud->update('s_articles_attributes', 'underscore_test', 'string');

        $data = $this->getSimpleTestData();

        $definedTranslation = [
            [
                'shopId' => 2,
                'name' => 'English-Name',
                'description' => 'English-Description',
                'descriptionLong' => 'English-DescriptionLong',
                'shippingTime' => 'English-ShippingTime',
                'keywords' => 'English-Keywords',
                'packUnit' => 'English-PackUnit',
            ],
        ];

        for ($i = 1; $i <= 20; ++$i) {
            $definedTranslation[0]['__attribute_attr' . $i] = 'English-Attr' . $i;
        }
        $definedTranslation[0]['__attribute_underscore_test'] = 'Attribute with underscore';

        $data['translations'] = $definedTranslation;

        $article = $this->resource->create($data);
        $newData = $this->resource->getOne($article->getId());

        $savedTranslation = $newData['translations'][2];
        $definedTranslation = $definedTranslation[0];

        static::assertEquals($definedTranslation['name'], $savedTranslation['name']);
        static::assertEquals($definedTranslation['description'], $savedTranslation['description']);
        static::assertEquals($definedTranslation['descriptionLong'], $savedTranslation['descriptionLong']);
        static::assertEquals($definedTranslation['shippingTime'], $savedTranslation['shippingTime']);
        static::assertEquals($definedTranslation['keywords'], $savedTranslation['keywords']);
        static::assertEquals($definedTranslation['packUnit'], $savedTranslation['packUnit']);
        static::assertEquals($definedTranslation['__attribute_underscore_test'], $savedTranslation['__attribute_underscore_test']);

        for ($i = 1; $i <= 20; ++$i) {
            $attr = '__attribute_attr' . $i;
            static::assertEquals($definedTranslation[$attr], $savedTranslation[$attr]);
        }

        $crud->delete('s_articles_attributes', 'underscore_test');
    }

    public function testBase64ImageUpload(): void
    {
        $data = $this->getSimpleTestData();

        $data['images'] = [
            [
                'link' => 'data:image/png;base64,' . require (__DIR__ . '/fixtures/base64image.php'),
            ],
        ];

        $model = $this->resource->create($data);
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($model->getId());

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        static::assertCount(\count($data['images']), $article['images']);
        foreach ($article['images'] as $image) {
            $key = 'media/image/' . $image['path'] . '.' . $image['extension'];
            static::assertTrue($mediaService->has($key));

            $imageContent = $mediaService->read($key);

            $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $imageContent);
            static::assertEquals('image/png', $mimeType);
        }
    }

    public function testImageReplacement(): void
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $createdIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            [
                ':articleId' => $article->getId(),
            ]
        );

        $data = [
            '__options_images' => ['replace' => true],
            'images' => $this->getImagesForNewArticle(100),
        ];

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            [
                ':articleId' => $article->getId(),
            ]
        );

        foreach ($updateIds as $id) {
            static::assertNotContains($id, $createdIds);
        }
        static::assertCount(5, $updateIds);
    }

    public function testImageReplacementMerge(): void
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $data = [
            '__options_images' => ['replace' => false],
            'images' => $this->getImagesForNewArticle(40),
        ];

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            [
                ':articleId' => $article->getId(),
            ]
        );

        static::assertCount(10, $updateIds);
    }

    public function testImageReplacementWithoutOption(): void
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewArticle();
        $article = $this->resource->create($data);

        $data = [
            'images' => $this->getImagesForNewArticle(40),
        ];

        $this->resource->update($article->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :articleId',
            [
                ':articleId' => $article->getId(),
            ]
        );

        static::assertCount(10, $updateIds);
    }

    public function testImageAttributes(): void
    {
        $data = $this->getSimpleTestData();
        $images = $this->getImagesForNewArticle();
        foreach ($images as &$image) {
            $image['attribute'] = [
                'attribute1' => 'attr1',
            ];
        }
        unset($image);
        $data['images'] = $images;
        foreach ($this->resource->create($data)->getImages() as $image) {
            static::assertInstanceOf(ArticleImage::class, $image->getAttribute());
            static::assertEquals('attr1', $image->getAttribute()->getAttribute1());
            static::assertNull($image->getAttribute()->getAttribute2());
            static::assertNull($image->getAttribute()->getAttribute3());
        }
    }

    public function testCreateWithDuplicateProperties(): void
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['values', 'option'])->from(Value::class, 'values')->innerJoin(
            'values.option',
            'option'
        )->setFirstResult(0)->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = [];
        foreach ($databaseValues as $value) {
            $valueIds[] = $value['id'];
            $optionIds[] = $value['option']['id'];
            $properties[] = [
                'value' => $value['value'],
                'option' => [
                    'name' => $value['option']['name'],
                ],
            ];
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
            static::assertContains($value['id'], $valueIds);
            static::assertContains($value['optionId'], $optionIds);
        }
    }

    public function testCreateWithMultiplePropertiesAndNewGroup(): void
    {
        $data = $this->getSimpleTestData();

        $optionName = 'newOption' . uniqid((string) rand());
        $properties = [
            [
                'option' => ['name' => $optionName],
                'value' => 'testValue',
            ],
            [
                'option' => ['name' => $optionName],
                'value' => 'anotherTestValue',
            ],
        ];

        $data['propertyValues'] = $properties;
        $data['filterGroupId'] = 1;
        $article = $this->resource->create($data);
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $articleId = $article->getId();
        $article = $this->resource->getOne($articleId);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['option'])->from(\Shopware\Models\Property\Option::class, 'option')->where(
            'option.name = :optionName'
        )->setParameter('optionName', $optionName)->setFirstResult(0)->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        static::assertEquals($article['propertyValues'][0]['optionId'], $article['propertyValues'][1]['optionId']);
        static::assertCount(1, $databaseValuesOptions);

        $this->resource->delete($articleId);

        //delete test values in s_filter_values
        $sql = 'DELETE FROM `s_filter_values` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        //delete test values in s_filter_relations
        $sql = 'DELETE FROM `s_filter_relations` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        //delete test values in s_filter_options
        $builder->delete(\Shopware\Models\Property\Option::class, 'option')->andWhere(
            'option.name = :optionName'
        )->setParameter('optionName', $optionName)->getQuery()->execute();
    }

    public function testUpdateWithDuplicateProperties(): void
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['values', 'option'])->from(Value::class, 'values')->innerJoin(
            'values.option',
            'option'
        )->setFirstResult(0)->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = [];
        foreach ($databaseValues as $value) {
            $valueIds[] = $value['id'];
            $optionIds[] = $value['option']['id'];
            $properties[] = [
                'value' => $value['value'],
                'option' => [
                    'name' => $value['option']['name'],
                ],
            ];
        }
        $update = [
            'propertyValues' => $properties,
            'filterGroupId' => 1,
        ];
        $data = $this->getSimpleTestData();
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );
        $article = $this->resource->create($data);
        $article = $this->resource->update($article->getId(), $update);
        foreach ($article->getPropertyValues() as $value) {
            static::assertContains($value->getId(), $valueIds);
            static::assertContains($value->getOption()->getId(), $optionIds);
        }
    }

    public function testPriceReplacement(): void
    {
        $data = $this->getSimpleTestData();
        $article = $this->resource->create($data);

        $update = [
            'mainDetail' => [
                'number' => $article->getMainDetail()->getNumber(),
                '__options_prices' => ['replace' => false],
                'prices' => [
                    [
                        'customerGroupKey' => 'H',
                        'from' => 1,
                        'to' => '10',
                        'price' => 200,
                    ],
                    [
                        'customerGroupKey' => 'H',
                        'from' => 11,
                        'to' => '-',
                        'price' => 100,
                    ],
                ],
            ],
        ];

        $article = $this->resource->update($article->getId(), $update);
        static::assertCount(3, $article->getMainDetail()->getPrices());
    }

    public function testUpdateWithMultiplePropertiesAndNewGroup(): void
    {
        $optionName = 'newOption' . uniqid((string) rand());
        $properties = [
            [
                'option' => ['name' => $optionName],
                'value' => 'testValue',
            ],
            [
                'option' => ['name' => $optionName],
                'value' => 'anotherTestValue',
            ],
        ];

        $update = [
            'propertyValues' => $properties,
            'filterGroupId' => 1,
        ];
        $data = $this->getSimpleTestData();
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );
        $article = $this->resource->create($data);
        $article = $this->resource->update($article->getId(), $update);

        $articleId = $article->getId();
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $article = $this->resource->getOne($article->getId());

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['option'])->from(\Shopware\Models\Property\Option::class, 'option')->where(
            'option.name = :optionName'
        )->setParameter('optionName', $optionName)->setFirstResult(0)->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        static::assertEquals($article['propertyValues'][0]['optionId'], $article['propertyValues'][1]['optionId']);
        static::assertCount(1, $databaseValuesOptions);

        $this->resource->delete($articleId);

        //delete test values in s_filter_values
        $sql = 'DELETE FROM `s_filter_values` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        //delete test values in s_filter_relations
        $sql = 'DELETE FROM `s_filter_relations` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        //delete test values in s_filter_options
        $builder->delete(\Shopware\Models\Property\Option::class, 'option')->andWhere(
            'option.name = :optionName'
        )->setParameter('optionName', $optionName)->getQuery()->execute();
    }

    public function testImageConfiguration(): void
    {
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );

        $create = $this->getSimpleTestData();

        $images = $this->getEntityOffset(
            Media::class,
            0,
            1,
            ['id as mediaId']
        );

        $configurator = $this->getSimpleConfiguratorSet(1, 2);
        $variants = $this->createConfiguratorVariants($configurator['groups']);

        $usedOption = $this->getOptionsForImage($configurator, 1, 'name');
        foreach ($images as &$image) {
            $image['options'] = [$usedOption];
        }
        unset($image);

        $create['images'] = $images;
        $create['configuratorSet'] = $configurator;
        $create['variants'] = $variants;

        $article = $this->resource->create($create);

        foreach ($article->getImages() as $image) {
            static::assertCount(1, $image->getMappings());

            foreach ($image->getMappings() as $mapping) {
                static::assertCount(1, $mapping->getRules());
            }
        }

        $this->resource->generateVariantImages($article);

        $article = $this->resource->getOne($article->getId());

        foreach ($article->getDetails() as $variant) {
            foreach ($variant->getConfiguratorOptions() as $option) {
                if ($option->getName() == $usedOption[0]['name']) {
                    static::assertCount(1, $variant->getImages());
                }
            }
        }
    }

    public function testCategoryReplacement(): void
    {
        $this->internalTestReplaceMode(
            Category::class,
            'categories'
        );
        $this->internalTestReplaceMode(
            Category::class,
            'categories',
            false
        );
    }

    public function testSimilarReplacement(): void
    {
        $this->internalTestReplaceMode(
            ProductModel::class,
            'similar'
        );
        $this->internalTestReplaceMode(
            ProductModel::class,
            'similar',
            false
        );
    }

    public function testRelatedReplacement(): void
    {
        $this->internalTestReplaceMode(
            ProductModel::class,
            'related'
        );
        $this->internalTestReplaceMode(
            ProductModel::class,
            'related',
            false
        );
    }

    public function testCustomerGroupReplacement(): void
    {
        $this->internalTestReplaceMode(CustomerGroup::class, 'customerGroups');
        $this->internalTestReplaceMode(CustomerGroup::class, 'customerGroups', false);
    }

    public function testArticleDefaultPriceBehavior(): void
    {
        $data = $this->getSimpleTestData();

        $product = $this->resource->create($data);

        static::assertInstanceOf(ProductModel::class, $product);

        $price = $product->getMainDetail()->getPrices()->first();

        static::assertEquals(
            400 / (((float) $product->getTax()->getTax() + 100) / 100),
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $data = $this->resource->getOne($product->getId());

        static::assertEquals(
            400 / (((float) $product->getTax()->getTax() + 100) / 100),
            $data['mainDetail']['prices'][0]['price']
        );
    }

    public function testSimilarWithNumber(): void
    {
        $products = $this->getEntityOffset(ProductModel::class, 0, 3);

        $data = $this->getSimpleTestData();
        $similar = [];
        foreach ($products as $product) {
            $model = Shopware()->Models()->find(ProductModel::class, $product['id']);

            $similar[] = ['number' => $model->getMainDetail()->getNumber()];
        }

        $data['similar'] = $similar;

        $product = $this->resource->create($data);

        static::assertNotEmpty($product->getSimilar());
    }

    public function testRelatedWithNumber(): void
    {
        $products = $this->getEntityOffset(ProductModel::class, 0, 3);

        $data = $this->getSimpleTestData();
        $similar = [];
        foreach ($products as $product) {
            $model = Shopware()->Models()->find(ProductModel::class, $product['id']);

            $similar[] = ['number' => $model->getMainDetail()->getNumber()];
        }

        $data['related'] = $similar;

        $product = $this->resource->create($data);

        static::assertNotEmpty($product->getRelated());
    }

    public function testDownloads(): void
    {
        $data = $this->getSimpleTestData();

        $data['downloads'] = [
            ['link' => 'data:image/png;base64,' . require (__DIR__ . '/fixtures/base64image.php')],
        ];

        $product = $this->resource->create($data);

        static::assertCount(1, $product->getDownloads());

        $downloads = [
            ['id' => $product->getDownloads()->first()->getId()],
            ['link' => 'file://' . __DIR__ . '/fixtures/variant-image.png'],
        ];

        $update = $this->resource->update(
            $product->getId(),
            [
                'downloads' => $downloads,
                '__options_downloads' => ['replace' => false],
            ]
        );

        static::assertCount(2, $update->getDownloads());
    }

    public function testSeoCategories(): void
    {
        $data = $this->getSimpleTestData();

        $data['categories'] = Shopware()->Db()->fetchAll('SELECT DISTINCT id FROM s_categories LIMIT 5, 10');

        $first = $data['categories'][3];
        $second = $data['categories'][4];

        $ids = [(int) $first['id'], (int) $second['id']];

        $data['seoCategories'] = [
            ['shopId' => 1, 'categoryId' => $first['id']],
            ['shopId' => 2, 'categoryId' => $second['id']],
        ];

        $product = $this->resource->create($data);

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        $product = $this->resource->getOne($product->getId());
        static::assertInstanceOf(ProductModel::class, $product);

        static::assertCount(2, $product->getSeoCategories());

        foreach ($product->getSeoCategories() as $category) {
            static::assertContains($category->getCategory()->getId(), $ids);
            static::assertContains($category->getShop()->getId(), [1, 2]);
        }
    }

    public function testArticleGrossPrices(): void
    {
        $data = $this->getSimpleTestData();

        $article = $this->resource->create($data);

        static::assertInstanceOf(ProductModel::class, $article);

        $price = $article->getMainDetail()->getPrices()->first();

        $net = 400 / (((float) $article->getTax()->getTax() + 100) / 100);

        static::assertEquals(
            $net,
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $this->resource->setResultMode(2);

        $data = $this->resource->getOne(
            $article->getId(),
            [
                'considerTaxInput' => true,
            ]
        );

        $price = $data['mainDetail']['prices'][0];

        static::assertEquals(400, $price['price']);
        static::assertEquals($net, $price['net']);
    }

    public function testAssignCategoriesByPathShouldBeSuccessful(): void
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
            [
                'name' => 'Hähnchenschnitzel Hollo',
                'active' => true,
                'tax' => 19,
                'supplier' => 'Onkel Tom',
                'categories' => [
                    ['path' => 'Deutsch|Genusswelten|Tees und Zubehör|Tees'],
                    ['path' => 'Deutsch|Genusswelten|Tees und Zubehör|Süßstoff'],
                    ['id' => 16],
                ],
                'mainDetail' => [
                    'number' => 'hollo-1',
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 4.99,
                        ],
                    ],
                ],
            ]
        );
        $ids = array_map(
            function ($category) {
                /* @var Category $category */
                return $category->getId();
            },
            $article->getCategories()->toArray()
        );
        $ids = array_flip($ids);
        static::assertArrayHasKey(12, $ids);
        static::assertArrayHasKey(16, $ids);
        static::assertCount(3, $ids);
    }

    public function testBatchModeShouldBeSuccessful(): void
    {
        $createNew = $this->getSimpleTestData();
        $updateExistingByNumber = [
            'mainDetail' => [
                'number' => 'SW10003',
            ],
            'keywords' => 'newKeyword1',
        ];
        $updateExistingById = [
            'id' => 3,
            'keywords' => 'newKeyword2',
        ];

        $result = $this->resource->batch(
            [
                'new' => $createNew,
                'existingByNumber' => $updateExistingByNumber,
                'existingById' => $updateExistingById,
            ]
        );

        static::assertEquals('newKeyword1', $result['existingByNumber']['data']['keywords']);
        static::assertEquals('newKeyword2', $result['existingById']['data']['keywords']);
        static::assertEquals('Testartikel', $result['new']['data']['name']);
    }

    public function testBatchDeleteShouldBeSuccessful(): void
    {
        $result = $this->resource->batch(
            [
                $this->getSimpleTestData(),
                $this->getSimpleTestData(),
                $this->getSimpleTestData(),
            ]
        );

        $delete = [];
        foreach ($result as $item) {
            $delete[] = $item['data'];
        }

        $result = $this->resource->batchDelete($delete);

        static::assertCount(3, $result);
    }

    public function testCategoryAssignment(): void
    {
        $number = 'CategoryAssignment' . uniqid((string) rand());

        $data = $this->getSimpleTestData();
        $data['mainDetail']['number'] = $number;

        $categories = Shopware()->Db()->fetchAll('SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2');
        $data['categories'] = $categories;

        $article = $this->resource->create($data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$article->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$article->getId()]
        );

        static::assertCount(2, $normal);
        static::assertCount(4, $denormalized);

        foreach ($categories as $category) {
            static::assertContains($category['id'], $normal);
            static::assertContains($category['id'], $denormalized);
        }

        $rewriteCategories = Shopware()->Db()->fetchAll(
            'SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2, 2'
        );
        $data = [
            'categories' => $rewriteCategories,
        ];

        $this->resource->update($article->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$article->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$article->getId()]
        );

        static::assertCount(2, $normal);
        static::assertCount(4, $denormalized);

        foreach ($rewriteCategories as $category) {
            static::assertContains($category['id'], $normal);
            static::assertContains(
                $category['id'],
                $denormalized,
                'Denormalized array contains not the expected category id'
            );
        }

        $additionally = Shopware()->Db()->fetchAll('SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2');
        $data = [
            '__options_categories' => ['replace' => false],
            'categories' => $additionally,
        ];
        $this->resource->update($article->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$article->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$article->getId()]
        );

        static::assertCount(4, $normal);
        static::assertCount(8, $denormalized);

        foreach ($rewriteCategories as $category) {
            static::assertContains($category['id'], $normal);
            static::assertContains(
                $category['id'],
                $denormalized,
                'Denormalized array contains not the expected category id'
            );
        }

        foreach ($additionally as $category) {
            static::assertContains($category['id'], $normal);
            static::assertContains(
                $category['id'],
                $denormalized,
                'Denormalized array contains not the expected category id'
            );
        }
    }

    public function testVariantImagesOnArticleCreate(): void
    {
        $data = [
            'descriptionLong' => 'test1',
            'name' => 'test1',
            'active' => true,
            'configuratorSet' => [
                'type' => 2,
                'groups' => [
                    [
                        'name' => 'New1',
                        'options' => [
                            ['name' => 'NewVal1'],
                            ['name' => 'Newval2'],
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'mainDetail' => [
                'number' => uniqid((string) rand()),
                'active' => true,
                'prices' => [
                    [
                        'price' => 0.0,
                        'pseudoPrice' => 0.0,
                        'customerGroupKey' => 'EK',
                    ],
                ],
                'configuratorOptions' => [
                    [
                        'group' => 'New1',
                        'option' => 'NewVal1',
                    ],
                ],
                'shippingTime' => 7.0,
                'width' => 0,
                'inStock' => 2,
            ],
            'filterGroupId' => null,
            'images' => [
                    [
                        'position' => 0,
                        'main' => 1,
                        'mediaId' => 2,
                        'description' => '147quad1809 603994396334907 1063748094 n',
                        'options' => [
                            [
                                [
                                    'name' => 'NewVal1',
                                ],
                            ],
                        ],
                    ],
                    [
                        'position' => 0,
                        'main' => 2,
                        'mediaId' => 3,
                        'description' => 'IMG 7228',
                        'options' => [
                            [
                                [
                                    'name' => 'Newval2',
                                ],
                            ],
                        ],
                    ],
                ],
            'lastStock' => true,
            'variants' => [
                [
                    'number' => uniqid((string) rand()) . '.1',
                    'active' => true,
                    'prices' => [
                        [
                            'price' => 0.0,
                            'pseudoPrice' => 0.0,
                            'customerGroupKey' => 'EK',
                        ],
                    ],
                    'configuratorOptions' => [
                        [
                            'group' => 'New1',
                            'option' => 'Newval2',
                        ],
                    ],
                    'shippingTime' => 7.0,
                    'width' => 0,
                    'isMain' => 0,
                    'inStock' => 2,
                ],
            ],
        ];

        foreach ($this->resource->create($data)->getImages() as $image) {
            $media = $image->getMedia();

            static::assertCount(1, $image->getMappings());

            $mapping = array_shift($image->getMappings()->getValues());

            static::assertCount(1, $mapping->getRules());

            $rule = array_shift($mapping->getRules()->getValues());

            if ($media->getId() === 2) {
                static::assertEquals('NewVal1', $rule->getOption()->getName());
            } elseif ($media->getId() === 3) {
                static::assertEquals('Newval2', $rule->getOption()->getName());
            }
        }
    }

    public function testArticleCreateWithNoStock(): void
    {
        $data = [
            'name' => 'article without stock definition',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'withoutstock' . uniqid((string) rand()),
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'price' => 999,
                    ],
                ],
            ],
        ];

        $model = $this->resource->create($data);

        static::assertSame(0, $model->getMainDetail()->getInStock());
    }

    /**
     * Regression test for SW-21360
     */
    public function testUpdateLastStock(): void
    {
        $data = $this->getSimpleTestData();
        $id = $this->resource->create($data)->getId();

        /*
         * Ensure that the mainDetails lastStock Attribute can be set
         */
        foreach ([true, false, 1, 0, null] as $val) {
            $temp = $this->resource->update($id, ['mainDetail' => ['lastStock' => $val]]);

            static::assertEquals($val, $temp->getMainDetail()->getLastStock());
        }

        $this->resource->delete($id);
    }

    public function testDeletingAnArticleAlsoDeletesDetailsAndAttributes(): void
    {
        $entityManager = Shopware()->Models();
        $productRepository = $entityManager->getRepository(ProductModel::class);
        $detailRepository = $entityManager->getRepository(Detail::class);
        $attributeRepository = $entityManager->getRepository(\Shopware\Models\Attribute\Article::class);

        // Test preparation

        /* Create an article with one main detail and two additional details
         * (variants). */
        $testData = $this->getSimpleTestData();
        $configurator = $this->getSimpleConfiguratorSet(1, 2);
        $variants = array_map(
            // Add attribute values to each variant.
            /* While the values are not relevant to the test, and attributes
             * would still be created without them, this test should still work
             * should the logic around when to create attributes change. */
            function ($variant) {
                $variant['attribute'] = [
                    'attr1' => 'Free form text 1',
                    'attr2' => 'Free form text 2',
                ];

                return $variant;
            },
            // Automatically generate variants:
            $this->createConfiguratorVariants($configurator['groups'])
        );
        $testData['configuratorSet'] = $configurator;
        $testData['variants'] = $variants;

        /* Save the article, so that we can later test its removal and the
         * removal of its details and attributes. */
        $createdArticleId = $this->resource->create($testData)->getId();

        // Retrieve identifiers from the database which we need for this test.
        try {
            $createdProduct = $productRepository->find($createdArticleId);
            static::assertInstanceOf(ProductModel::class, $createdProduct);
            $createdDetails = array_merge(
                $createdProduct->getDetails()->toArray(),
                [$createdProduct->getMainDetail()]
            );

            /* The identifiers of the details the removal of which we will
             * later verify. */
            $createdDetailIds = array_unique(array_map(
                function ($createdDetail) {
                    return $createdDetail->getId();
                },
                $createdDetails
            ));

            $createdAttributes = array_map(
                function ($createdDetail) { return $createdDetail->getAttribute(); },
                $createdDetails
            );
            // Verify we did not break our test setup.
            static::assertCount(3, $createdDetailIds);

            /* The identifiers of the attributes the removal of which we will
             * later verify. */
            $createdAttributeIds = array_unique(array_map(
                function ($createdAttribute) { return $createdAttribute->getId(); },
                $createdAttributes
            ));
            static::assertCount(3, $createdAttributeIds);

            // Verify created article's details match test setup assumptions.
            static::assertCount(\count($createdDetailIds), $detailRepository->findBy([
                'id' => $createdDetailIds,
            ]));
            // Verify created article's attributes match test setup assumptions.
            static::assertCount(\count($createdAttributeIds), $attributeRepository->findBy([
                'id' => $createdAttributeIds,
            ]));
        } finally {
            // Even if the test setup fails, delete the test's created article.

            // Tested action:
            $this->resource->delete($createdArticleId);
        }

        static::assertNull(
            $productRepository->find($createdArticleId),
            sprintf(
                'Deletion of the article (id = %s) itself failed.',
                $createdArticleId
            )
        );

        // Test assertions
        static::assertCount(
            0,
            $detailRepository->findBy([
                'id' => $createdDetailIds,
            ]),
            sprintf(
                'Deletion of the article\'s (id = %s) details (%s) failed.',
                $createdArticleId,
                implode(', ', $createdDetailIds)
            )
        );
        static::assertCount(
            0,
            $attributeRepository->findBy([
                'id' => $createdAttributeIds,
            ]),
            sprintf(
                'Deletion of the article\'s (id = %s) details\' (%s) attributes (%s) failed.',
                $createdArticleId,
                implode(', ', $createdDetailIds),
                implode(', ', $createdAttributeIds)
            )
        );
    }

    /**
     * @group pcovAdapterBrokenTest
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInvalidProductInBatch(): void
    {
        $minimalTestArticle = [
            'id' => 2,
            'images' => [
                [
                    'mediaId' => 10,
                    'description' => null,
                ],
            ],
        ];

        $result = $this->resource->batch([$minimalTestArticle, $minimalTestArticle]);

        static::assertFalse($result[0]['success']);
        static::assertTrue(Shopware()->Models()->isOpen());
    }

    public function testDeletingPricesWorksOnReplace(): void
    {
        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'to' => 20,
                        'price' => 500,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'MeinKonf',
                'groups' => [
                    [
                        'name' => 'Farbe',
                        'options' => [
                            ['name' => 'Gelb'],
                            ['name' => 'grün'],
                        ],
                    ],
                    [
                        'name' => 'Gräße',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    // create a new unit
                    'unit' => [
                        'unit' => 'xyz',
                        'name' => 'newUnit',
                    ],
                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],
                    'configuratorOptions' => [
                        [
                            'option' => 'Gelb',
                            'group' => 'Farbe',
                        ],
                        [
                            'option' => 'XL',
                            'group' => 'Größe',
                        ],
                    ],
                    'minPurchase' => 5,
                    'purchaseSteps' => 2,
                    'prices' => [
                        [
                            'customerGroupKey' => 'H',
                            'to' => 20,
                            'price' => 500,
                        ],
                        [
                            'customerGroupKey' => 'H',
                            'from' => 21,
                            'to' => '-',
                            'price' => 400,
                        ],
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
            'categories' => [
                ['id' => 3],
            ],
        ];

        $article = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $article);
        static::assertGreaterThan(0, $article->getId());

        $countOfPrices = (int) Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_articles_prices');

        $testData['__options_variants']['replace'] = true;

        $this->resource->update($article->getId(), $testData);

        $currentCountOfPrices = (int) Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_articles_prices');

        static::assertEquals($countOfPrices, $currentCountOfPrices);
        $this->resource->delete($article->getId());
    }

    /**
     * Combinations merge the result of dimensional arrays not perfectly
     * so we have to clean up the first array level.
     *
     * @param array[] $combinations
     *
     * @return array[]
     */
    protected function cleanUpCombinations(array $combinations): array
    {
        foreach ($combinations as &$combination) {
            $combination[] = [
                'option' => $combination['option'],
                'groupId' => $combination['groupId'],
            ];
            unset($combination['groupId'], $combination['option']);
        }

        return $combinations;
    }

    /**
     * Helper function which combines all array elements
     * of the passed arrays.
     */
    protected function combinations(array $arrays, int $i = 0): array
    {
        if (!isset($arrays[$i])) {
            return [];
        }
        if ($i == \count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);

        $result = [];

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = \is_array($t) ? array_merge([$v], $t) : [$v, $t];
            }
        }

        return $result;
    }

    protected function internalTestReplaceMode($entity, $arrayKey, $replace = true): void
    {
        // Create keys for getter function and the __options parameter in the update and create
        // Example => "__options_categories"  /  "getCategories"
        $replaceKey = '__options_' . $arrayKey;
        $getter = 'get' . ucfirst($arrayKey);

        // Returns a simple article data set to create an article with a simple main detail
        $data = $this->getSimpleTestData();

        // Get an offset of 10 entities for the current entity type, like 10x categories
        $createdEntities = $this->getEntityOffset($entity);
        $data[$arrayKey] = $createdEntities;

        $article = $this->resource->create($data);
        static::assertCount(\count($createdEntities), $article->$getter());

        $updatedEntity = $this->getEntityOffset($entity, 20, 5);

        $update = [
            $replaceKey => ['replace' => $replace],
            $arrayKey => $updatedEntity,
        ];
        $article = $this->resource->update($article->getId(), $update);

        if ($replace == true) {
            static::assertCount(\count($updatedEntity), $article->$getter());
        } else {
            static::assertCount(\count($createdEntities) + \count($updatedEntity), $article->$getter());
        }
    }

    /**
     * @return array[]
     */
    private function getOptionsForImage($configuratorSet, $optionCount = null, $property = 'id'): array
    {
        if (!\is_int($optionCount)) {
            $optionCount = rand(1, \count($configuratorSet['groups']) - 1);
        }

        $options = [];
        foreach ($configuratorSet['groups'] as $group) {
            $id = rand(0, \count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = [
                $property => $option[$property],
            ];
            if (\count($options) == $optionCount) {
                return $options;
            }
        }

        return $options;
    }

    /**
     * Helper function which creates all variants for
     * the passed groups with options.
     */
    private function createConfiguratorVariants(
        array $groups,
        array $groupMapping = ['key' => 'groupId', 'value' => 'id'],
        array $optionMapping = ['key' => 'option', 'value' => 'name']
    ): array {
        $options = [];

        $groupArrayKey = $groupMapping['key'];
        $groupValuesKey = $groupMapping['value'];
        $optionArrayKey = $optionMapping['key'];
        $optionValuesKey = $optionMapping['value'];

        foreach ($groups as $group) {
            $groupOptions = [];
            foreach ($group['options'] as $option) {
                $groupOptions[] = [
                    $groupArrayKey => $group[$groupValuesKey],
                    $optionArrayKey => $option[$optionValuesKey],
                ];
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->combinations($options);
        $combinations = $this->cleanUpCombinations($combinations);

        $variants = [];
        foreach ($combinations as $combination) {
            $variant = $this->getSimpleVariantData();
            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;
        }

        return $variants;
    }

    /**
     * @return array[]
     */
    private function getImagesForNewArticle(int $offset = 10, int $limit = 5): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(
            [
                'media.id as mediaId',
                '2 as main',
            ]
        )->from(Media::class, 'media', 'media.id')->addOrderBy('media.id', 'ASC')->setFirstResult(
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

    /**
     * @return array<string, mixed>
     */
    private function getSimpleTestData(): array
    {
        return [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
        ];
    }

    /**
     * @param class-string<ModelEntity> $entity
     * @param string[]                  $fields
     *
     * @return array[]
     */
    private function getEntityOffset(string $entity, int $offset = 0, int $limit = 10, array $fields = ['id']): array
    {
        if (!empty($fields)) {
            $selectFields = [];
            foreach ($fields as $field) {
                $selectFields[] = 'alias.' . $field;
            }
        } else {
            $selectFields = ['alias'];
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($selectFields)->from($entity, 'alias')->setFirstResult($offset)->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @return array{name: string, groups: array}
     */
    private function getSimpleConfiguratorSet($groupLimit = 3, $optionLimit = 5): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['groups.id', 'groups.name'])->from(
            ConfiguratorGroup::class,
            'groups'
        )->setFirstResult(0)->setMaxResults($groupLimit)->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['options.id', 'options.name'])->from(
            Option::class,
            'options'
        )->where('options.groupId = :groupId')->setFirstResult(0)->setMaxResults($optionLimit)->orderBy(
            'options.position',
            'ASC'
        );

        foreach ($groups as &$group) {
            $builder->setParameter('groupId', $group['id']);
            $group['options'] = $builder->getQuery()->getArrayResult();
        }

        return [
            'name' => 'Test-Set',
            'groups' => $groups,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSimpleVariantData(): array
    {
        return [
            'number' => 'swTEST' . uniqid((string) rand()),
            'inStock' => 100,
            'unitId' => 1,
            'prices' => [
                [
                    'customerGroupKey' => 'EK',
                    'from' => 1,
                    'to' => '-',
                    'price' => 400,
                ],
            ],
        ];
    }

    /**
     * @param array{name: string, groups: array} $configuratorSet
     *
     * @return array<array>
     */
    private function getVariantOptionsOfSet(array $configuratorSet): array
    {
        $options = [];
        foreach ($configuratorSet['groups'] as $group) {
            $id = rand(0, \count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = [
                'optionId' => $option['id'],
                'groupId' => $group['id'],
            ];
        }

        return $options;
    }

    private function createConfiguratorSetProduct(): ProductModel
    {
        return $this->resource->create(
            [
                'name' => 'Turnschuhe',
                'active' => true,
                'tax' => 19,
                'supplier' => 'Turnschuhe Inc.',
                'categories' => [
                    ['id' => 15],
                ],
                'mainDetail' => [
                    'number' => 'turn',
                    'prices' => [
                        [
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ],
                    ],
                ],
            ]
        );
    }
}
