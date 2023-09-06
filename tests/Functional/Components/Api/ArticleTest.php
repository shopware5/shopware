<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\Api;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Article as ProductApiResource;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Article\Download;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Attribute\ArticleImage as ProductImage;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media;
use Shopware\Models\Property\Option as PropertyOption;
use Shopware\Models\Property\Value;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ArticleTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var ProductApiResource
     */
    protected $resource;

    public function createResource(): ProductApiResource
    {
        return new ProductApiResource();
    }

    public function testCreateShouldBeSuccessful(): int
    {
        $testData = $this->getTestData();
        $product = $this->createProduct($testData);

        static::assertSame($product->getName(), $testData['name']);
        static::assertSame($product->getDescription(), $testData['description']);
        static::assertSame($product->getMetaTitle(), $testData['metaTitle']);

        static::assertSame($product->getDescriptionLong(), $testData['descriptionLong']);

        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        static::assertInstanceOf(ProductAttribute::class, $product->getMainDetail()->getAttribute());
        // Check attributes of main variant
        static::assertSame(
            $product->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        static::assertSame(
            $product->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );

        // Check attributes of non-main variant
        $variant = $product->getDetails()->matching(Criteria::create()->where(
            Criteria::expr()->eq('number', $testData['variants'][0]['number'])
        ));
        static::assertSame(
            $variant->first()->getAttribute()->getAttr3(),
            $testData['variants'][0]['attribute']['attr3']
        );
        static::assertSame(
            $variant->first()->getAttribute()->getAttr4(),
            $testData['variants'][0]['attribute']['attr4']
        );

        $propertyValues = $product->getPropertyValues()->getValues();
        static::assertCount(\count($testData['propertyValues']), $propertyValues);
        foreach ($propertyValues as $propertyValue) {
            static::assertContains($propertyValue->getValue(), ['grün', 'testWert']);
        }

        static::assertInstanceOf(Tax::class, $product->getTax());
        static::assertSame($testData['taxId'], $product->getTax()->getId());

        static::assertCount(2, $product->getCategories());
        static::assertCount(2, $product->getRelated());
        static::assertCount(2, $product->getSimilar());
        static::assertCount(2, $product->getLinks());
        static::assertCount(2, $product->getMainDetail()->getPrices());
        foreach ($product->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }
        foreach ($product->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                static::assertGreaterThan(0, $price->getFrom());
            }
        }

        return $product->getId();
    }

    public function testCreateWithNewUnitShouldBeSuccessful(): int
    {
        $testData = [
            'name' => 'Testproduct',
            'description' => 'testdescription',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',
            'tax' => 19,
            'categories' => [
                ['id' => 15],
                ['id' => 10],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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

        $product = $this->createProduct($testData);
        // change number for second product
        $testData['mainDetail']['number'] = 'swTEST' . uniqid((string) mt_rand());
        $secondProduct = $this->createProduct($testData);

        static::assertSame($testData['name'], $product->getName());
        static::assertSame($testData['description'], $product->getDescription());
        static::assertNull($product->getMetaTitle());

        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        foreach ($product->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }

        static::assertInstanceOf(Unit::class, $product->getMainDetail()->getUnit());
        static::assertGreaterThan(0, $product->getMainDetail()->getUnit()->getId());
        static::assertSame($testData['mainDetail']['unit']['name'], $product->getMainDetail()->getUnit()->getName());
        static::assertSame($testData['mainDetail']['unit']['unit'], $product->getMainDetail()->getUnit()->getUnit());

        static::assertInstanceOf(ProductVariant::class, $secondProduct->getMainDetail());
        static::assertInstanceOf(Unit::class, $secondProduct->getMainDetail()->getUnit());
        static::assertSame($product->getMainDetail()->getUnit()->getId(), $secondProduct->getMainDetail()->getUnit()->getId());

        return $product->getId();
    }

    /*
     * Test that empty product attributes are created
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
                'number' => 'swAttr1' . uniqid((string) mt_rand(), true),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    ['customerGroupKey' => 'EK', 'from' => 1, 'to' => '-', 'price' => 400],
                ],
                'configuratorOptions' => $this->getVariantOptionsOfSet($configurator),
            ],
            'variants' => [
                [
                    'number' => 'swAttr2' . uniqid((string) mt_rand(), true),
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

        $product = $this->resource->create($testData);

        // Load actual database model
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $readProduct = $this->resource->getOne($product->getId());
        static::assertInstanceOf(ProductModel::class, $readProduct);

        static::assertCount(2, $readProduct->getDetails());
        foreach ($readProduct->getDetails() as $variant) {
            static::assertNotNull($variant->getAttribute());
            static::assertNull($variant->getAttribute()->getAttr1());
        }
    }

    /**
     * Test that creating a product with images generates thumbnails
     *
     * @return int Product ID
     */
    public function testCreateWithImageShouldCreateThumbnails(): int
    {
        $testData = [
            'name' => 'Test product with images',
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
                        'name' => 'neueOption' . uniqid((string) mt_rand()),
                    ],
                ],
            ],
            'images' => [
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                ],
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/variant-image.png',
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand()),
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

        $product = $this->createProduct($testData);
        static::assertCount(3, $product->getImages());

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($product->getImages() as $image) {
            static::assertInstanceOf(Media::class, $image->getMedia());
            static::assertCount(4, $image->getMedia()->getThumbnails());
            foreach ($image->getMedia()->getThumbnails() as $thumbnail) {
                static::assertTrue($mediaService->has($thumbnail));
            }
        }
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        foreach ($product->getMainDetail()->getPrices() as $price) {
            static::assertGreaterThan(0, $price->getFrom());
        }
        foreach ($product->getDetails() as $variant) {
            foreach ($variant->getPrices() as $price) {
                static::assertGreaterThan(0, $price->getFrom());
            }
        }

        return $product->getId();
    }

    public function testFlipProductMainVariantShouldBeSuccessful(): void
    {
        $id = $this->testCreateWithImageShouldCreateThumbnails();
        $originalProduct = $this->resource->getOne($id);
        static::assertIsArray($originalProduct);
        $mainVariantNumber = (string) $originalProduct['mainDetailId'];

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

        $product = $this->resource->update($id, $testData);

        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        static::assertSame($mainVariantNumber, $product->getMainDetail()->getNumber());
    }

    /**
     * Test that updating a Product with images generates thumbnails
     */
    public function testUpdateWithImageShouldCreateThumbnails(): void
    {
        $testData = [
            'images' => [
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                ],
            ],
        ];

        $id = $this->testCreateWithImageShouldCreateThumbnails();
        $product = $this->resource->update($id, $testData);
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertGreaterThan(0, $product->getId());

        static::assertCount(4, $product->getImages());
        foreach ($product->getImages() as $image) {
            static::assertInstanceOf(Media::class, $image->getMedia());
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
            'name' => 'Test product with images and right proportions',
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
                        'name' => 'neueOption' . uniqid((string) mt_rand()),
                    ],
                ],
            ],
            'images' => [
                [
                    'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand()),
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

        $product = $this->createProduct($testData);
        static::assertCount(2, $product->getImages());

        $proportionalSizes = [
            '200x200',
            '600x600',
            '1280x1280',
            '140x140',
        ];

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($product->getImages() as $image) {
            static::assertInstanceOf(Media::class, $image->getMedia());
            $thumbnails = $image->getMedia()->getThumbnails();
            static::assertCount(4, $thumbnails);
            $thumbnails = array_values($thumbnails);

            foreach ($thumbnails as $key => $thumbnail) {
                static::assertTrue($mediaService->has($thumbnail));

                $mediaPath = $mediaService->read($thumbnail);
                static::assertIsString($mediaPath);
                $image = imagecreatefromstring($mediaPath);
                static::assertNotFalse($image);
                $width = imagesx($image);
                $height = imagesy($image);

                static::assertSame($proportionalSizes[$key], $width . 'x' . $height);
            }
        }

        $this->resource->delete($product->getId());
    }

    /**
     * Test creating a product with new configurator set and multiple variants SW-7925
     */
    public function testCreateWithVariantsAndNewConfiguratorSetShouldBeSuccessful(): void
    {
        $testData = [
            'name' => 'Test product',
            'description' => 'Test description',
            'descriptionLong' => 'Long test description',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',
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
                        'name' => 'neueOption' . uniqid((string) mt_rand()),
                    ],
                ],
            ],
            'mainDetail' => [
                'number' => 'swConfigSetMainTest' . uniqid((string) mt_rand()),
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
                    'number' => 'swConfigSetMainTest.variant.' . uniqid((string) mt_rand()),
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
                    'number' => 'swConfigSetMainTest.variant.' . uniqid((string) mt_rand()),
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

        $product = $this->createProduct($testData);

        static::assertSame($product->getName(), $testData['name']);
        static::assertSame($product->getDescription(), $testData['description']);
        static::assertSame($product->getMetaTitle(), $testData['metaTitle']);

        static::assertSame($product->getDescriptionLong(), $testData['descriptionLong']);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        static::assertInstanceOf(ProductAttribute::class, $product->getMainDetail()->getAttribute());
        static::assertSame(
            $product->getMainDetail()->getAttribute()->getAttr1(),
            $testData['mainDetail']['attribute']['attr1']
        );
        static::assertSame(
            $product->getMainDetail()->getAttribute()->getAttr2(),
            $testData['mainDetail']['attribute']['attr2']
        );

        $propertyValues = $product->getPropertyValues()->getValues();
        static::assertCount(\count($testData['propertyValues']), $propertyValues);
        foreach ($propertyValues as $propertyValue) {
            static::assertContains($propertyValue->getValue(), ['grün', 'testWert']);
        }

        static::assertInstanceOf(Tax::class, $product->getTax());
        static::assertSame($testData['taxId'], $product->getTax()->getId());

        static::assertCount(2, $product->getCategories());
        static::assertCount(0, $product->getRelated());
        static::assertCount(0, $product->getSimilar());
        static::assertCount(2, $product->getLinks());
        static::assertCount(2, $product->getMainDetail()->getPrices());

        $groups = Shopware()->Models()->getRepository(ConfiguratorGroup::class)->findBy(
            ['name' => ['Group1', 'Group2']]
        );

        foreach ($groups as $group) {
            Shopware()->Models()->remove($group);
        }

        $this->resource->delete($product->getId());
    }

    public function testGetOneByNumberShouldBeSuccessful(): void
    {
        $id = $this->createProduct($this->getTestData())->getId();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $product = $this->resource->getOne($id);
        static::assertInstanceOf(ProductModel::class, $product);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        $number = $product->getMainDetail()->getNumber();
        static::assertIsString($number);
        $product = $this->resource->getOneByNumber($number);
        static::assertInstanceOf(ProductModel::class, $product);
        static::assertSame($id, $product->getId());
    }

    public function testGetOneShouldBeSuccessful(): void
    {
        $id = $this->createProduct($this->getTestData())->getId();
        $product = $this->resource->getOne($id);
        static::assertIsArray($product);
        static::assertGreaterThan(0, $product['id']);
    }

    public function testGetOneShouldBeAbleToReturnObject(): void
    {
        $id = $this->createProduct($this->getTestData())->getId();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $product = $this->resource->getOne($id);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertGreaterThan(0, $product->getId());
    }

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
     */
    public function testGetListShouldUseCorrectDetailsAttribute(): void
    {
        $id = $this->createProduct($this->getTestData())->getId();
        // Filter with attribute of main variant => product found
        $result = $this->resource->getList(0, 1, [
            'id' => $id,
            'attribute.attr1' => 'Freitext1', // Belongs to main variant
        ]);

        static::assertSame(1, $result['total']);
        static::assertSame($id, $result['data'][0]['id'], (string) $id);

        // Filter with attribute of other (non-main) variant => no result
        $result = $this->resource->getList(0, 1, [
            'id' => $id,
            'attribute.attr3' => 'Freitext3',
        ]);

        static::assertSame(0, $result['total']);
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

    public function testUpdateByNumberShouldBeSuccessful(): ?string
    {
        $id = $this->createProduct($this->getTestData())->getId();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $product = $this->resource->getOne($id);
        static::assertInstanceOf(ProductModel::class, $product);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        $number = $product->getMainDetail()->getNumber();
        static::assertIsString($number);

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

        $product = $this->resource->updateByNumber($number, $testData);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertSame($id, $product->getId());
        static::assertSame($product->getDescription(), $testData['description']);
        static::assertSame($product->getDescriptionLong(), $testData['descriptionLong']);

        static::assertInstanceOf(Supplier::class, $product->getSupplier());
        static::assertSame($testData['supplierId'], $product->getSupplier()->getId());

        $propertyValues = $product->getPropertyValues()->getValues();
        static::assertCount(\count($propertyValues), $testData['propertyValues']);

        // Categories should be updated
        static::assertCount(1, $product->getCategories());

        // Related should be untouched
        static::assertCount(2, $product->getRelated());

        // Similar should be removed
        static::assertCount(0, $product->getSimilar());

        return $number;
    }

    public function testUpdateShouldBeSuccessful(): int
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

        $id = $this->createProduct($this->getTestData())->getId();
        $product = $this->resource->update($id, $testData);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertSame($id, $product->getId());
        static::assertSame($product->getDescription(), $testData['description']);
        static::assertSame($product->getDescriptionLong(), $testData['descriptionLong']);
        static::assertInstanceOf(Supplier::class, $product->getSupplier());
        static::assertSame($testData['supplierId'], $product->getSupplier()->getId());

        $propertyValues = $product->getPropertyValues()->getValues();
        static::assertCount(\count($propertyValues), $testData['propertyValues']);

        // Categories should be updated
        static::assertCount(1, $product->getCategories());

        // Related should be untouched
        static::assertCount(2, $product->getRelated());

        // Similar should be removed
        static::assertCount(0, $product->getSimilar());

        return $id;
    }

    public function testUpdateWithInvalidDataShouldThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        // required field name is blank
        $testData = [
            'name' => ' ',
            'description' => 'Update description',
            'descriptionLong' => 'Update descriptionLong',
        ];

        $id = $this->createProduct($this->getTestData())->getId();
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
        $this->resource->update(0, []);
    }

    public function testDeleteShouldBeSuccessful(): void
    {
        $id = $this->createProduct($this->getTestData())->getId();
        $product = $this->resource->delete($id);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertSame(0, (int) $product->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete(0);
    }

    /**
     * Test case to add a new product image over a media id.
     */
    public function testAddProductMediaOverMediaId(): void
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
        $product = $this->resource->getOne(2);
        static::assertIsArray($product);

        $image = array_pop($product['images']);
        static::assertSame(25, $image['mediaId']);
    }

    public function testUpdateToVariantProduct(): void
    {
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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
        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertSame('Turnschuhe', $updated->getName(), 'Product name does not match');

        foreach ($updated->getDetails() as $variant) {
            static::assertContains(
                $variant->getNumber(),
                ['turn', 'turn.1', 'turn.2', 'turn.3'],
                'Variant number dont match'
            );

            static::assertCount(2, $variant->getConfiguratorOptions(), 'Configurator option count does not match');

            foreach ($variant->getConfiguratorOptions() as $option) {
                static::assertContains($option->getName(), ['M', 'S', 'blau', 'grün']);
            }
        }

        $this->resource->delete($updated->getId());
    }

    public function testUpdateToConfiguratorSetPosition(): void
    {
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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
        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertSame('Turnschuhe', $updated->getName(), 'Product name does not match');

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
                        static::assertSame(4, $option->getPosition());
                        break;
                    case 'S':
                        static::assertSame(123, $option->getPosition());
                        break;
                    case 'blau':
                        static::assertSame(11, $option->getPosition());
                        break;
                    case 'grün':
                        static::assertSame(99, $option->getPosition());
                        break;

                    default:
                        static::fail('There is an unknown variant.');
                }
            }
        }

        $this->resource->delete($updated->getId());
    }

    public function testUpdateToConfiguratorSetType(): void
    {
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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
        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertInstanceOf(Set::class, $updated->getConfiguratorSet());
        static::assertSame(2, $updated->getConfiguratorSet()->getType(), "ConfiguratorSet.Type doesn't match");

        $this->resource->delete($updated->getId());
    }

    public function testUpdateToConfiguratorSetPositionsShouldBeGenerated(): void
    {
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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
        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertInstanceOf(Set::class, $updated->getConfiguratorSet());
        $options = $updated->getConfiguratorSet()->getOptions();

        static::assertInstanceOf(ConfiguratorOption::class, $options[0]);
        static::assertSame(0, $options[0]->getPosition());
        static::assertInstanceOf(ConfiguratorOption::class, $options[1]);
        static::assertSame(1, $options[1]->getPosition());
        static::assertInstanceOf(ConfiguratorOption::class, $options[2]);
        static::assertSame(2, $options[2]->getPosition());

        $this->resource->delete($updated->getId());
    }

    public function testUpdateToConfiguratorSetPositionsShouldOverwritePositions(): void
    {
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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

        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertInstanceOf(Set::class, $updated->getConfiguratorSet());
        $options = $updated->getConfiguratorSet()->getOptions();

        static::assertInstanceOf(ConfiguratorOption::class, $options[0]);
        static::assertSame(5, $options[0]->getPosition());
        static::assertInstanceOf(ConfiguratorOption::class, $options[1]);
        static::assertSame(6, $options[1]->getPosition());
        static::assertInstanceOf(ConfiguratorOption::class, $options[2]);
        static::assertSame(11, $options[2]->getPosition());

        $this->resource->delete($updated->getId());
    }

    public function testUpdateToConfiguratorSetPositionsShouldRemainUntouched(): void
    {
        $this->testUpdateToConfiguratorSetPositionsShouldOverwritePositions();
        $product = $this->createConfiguratorSetProduct();

        $updateProduct = [
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

        $updated = $this->resource->update($product->getId(), $updateProduct);
        static::assertInstanceOf(Set::class, $updated->getConfiguratorSet());
        $options = $updated->getConfiguratorSet()->getOptions();

        $firstOption = $options[0];
        static::assertInstanceOf(ConfiguratorOption::class, $firstOption);
        static::assertSame(5, $firstOption->getPosition());
        $secondOption = $options[1];
        static::assertInstanceOf(ConfiguratorOption::class, $secondOption);
        static::assertSame(11, $secondOption->getPosition());

        $this->resource->delete($updated->getId());
    }

    public function testCreateUseConfiguratorId(): string
    {
        $configurator = $this->getSimpleConfiguratorSet(2);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);
        $variantNumber = 'swVariant' . uniqid((string) mt_rand());

        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 1,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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

        $product = $this->resource->create($testData);

        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $data = $this->resource->getOne($product->getId());
        static::assertIsArray($data);

        static::assertCount(2, $data['details'][0]['configuratorOptions']);

        return $variantNumber;
    }

    public function testUpdateUseConfiguratorIds(): void
    {
        $variantNumber = $this->testCreateUseConfiguratorId();
        $configurator = $this->getSimpleConfiguratorSet(2);
        $variantOptions = $this->getVariantOptionsOfSet($configurator);

        $id = (int) Shopware()->Db()->fetchOne(
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
        static::assertIsArray($data);
        static::assertCount(2, $data['details'][0]['configuratorOptions']);
    }

    public function testCreateWithMainImages(): int
    {
        $this->resource->setResultMode(
            Resource::HYDRATE_OBJECT
        );

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select([
                'media.id as mediaId',
                '2 as main',
            ])
            ->from(Media::class, 'media')
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
        $product = $this->resource->create($data);

        static::assertCount(4, $product->getImages());

        $mainFlagExists = false;

        foreach ($product->getImages() as $image) {
            if ($image->getMain() === 1) {
                $mainFlagExists = true;
                static::assertInstanceOf(Media::class, $image->getMedia());
                static::assertSame($expectedMainId, $image->getMedia()->getId());
            }
        }
        static::assertTrue($mainFlagExists);

        return $product->getId();
    }

    public function testUpdateWithSingleMainImage(): int
    {
        $productId = $this->testCreateWithMainImages();
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $product = $this->resource->getOne($productId);
        static::assertIsArray($product);

        $updateImages = [];
        $newId = null;
        foreach ($product['images'] as $image) {
            if ($image['main'] !== 1) {
                $updateImages['images'][] = [
                    'id' => $image['id'],
                    'main' => 1,
                ];
                $newId = $image['id'];
                break;
            }
        }
        $product = $this->resource->update($productId, $updateImages);

        static::assertCount(4, $product->getImages());

        $hasMain = false;
        foreach ($product->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                static::assertSame($image->getId(), $newId);
            }
        }
        static::assertTrue($hasMain);

        return $product->getId();
    }

    public function testUpdateWithMainImage(): void
    {
        $productId = $this->testUpdateWithSingleMainImage();
        $this->resource->getManager()->clear();

        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $product = $this->resource->getOne($productId);
        static::assertIsArray($product);

        $updateImages = [];
        $lastMainId = null;

        foreach ($product['images'] as $image) {
            $newImageData = [
                'id' => $image['id'],
                'main' => $image['main'],
            ];

            if ((int) $image['main'] === 1) {
                $lastMainId = $image['id'];
                $newImageData['main'] = 2;
            }

            $updateImages['images'][] = $newImageData;
        }

        static::assertArrayHasKey('images', $updateImages);
        $newMainId = null;
        foreach ($updateImages['images'] as &$image) {
            if ($image['id'] !== $lastMainId) {
                $image['main'] = 1;
                $newMainId = $image['id'];
                break;
            }
        }
        unset($image);
        $product = $this->resource->update($productId, $updateImages);
        static::assertCount(4, $product->getImages());

        $hasMain = false;
        foreach ($product->getImages() as $image) {
            if ($image->getMain() === 1) {
                $hasMain = true;
                static::assertSame($newMainId, $image->getId());
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

        Shopware()->Container()->get(Connection::class)->rollBack();
        $crud->update('s_articles_attributes', 'underscore_test', 'string');
        Shopware()->Container()->get(Connection::class)->beginTransaction();

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

        $product = $this->resource->create($data);
        $newData = $this->resource->getOne($product->getId());
        static::assertIsArray($newData);

        $savedTranslation = $newData['translations'][2];
        $definedTranslation = $definedTranslation[0];

        static::assertSame($definedTranslation['name'], $savedTranslation['name']);
        static::assertSame($definedTranslation['description'], $savedTranslation['description']);
        static::assertSame($definedTranslation['descriptionLong'], $savedTranslation['descriptionLong']);
        static::assertSame($definedTranslation['shippingTime'], $savedTranslation['shippingTime']);
        static::assertSame($definedTranslation['keywords'], $savedTranslation['keywords']);
        static::assertSame($definedTranslation['packUnit'], $savedTranslation['packUnit']);
        static::assertSame($definedTranslation['__attribute_underscore_test'], $savedTranslation['__attribute_underscore_test']);

        for ($i = 1; $i <= 20; ++$i) {
            $attr = '__attribute_attr' . $i;
            static::assertSame($definedTranslation[$attr], $savedTranslation[$attr]);
        }

        Shopware()->Container()->get(Connection::class)->rollBack();
        $crud->delete('s_articles_attributes', 'underscore_test');
        Shopware()->Container()->get(Connection::class)->beginTransaction();
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
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $product = $this->resource->getOne($model->getId());
        static::assertIsArray($product);

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($product['images'] as $image) {
            $key = 'media/image/' . $image['path'] . '.' . $image['extension'];
            static::assertTrue($mediaService->has($key));

            $imageContent = $mediaService->read($key);
            static::assertIsString($imageContent);

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            static::assertNotFalse($fileInfo);
            $mimeType = finfo_buffer($fileInfo, $imageContent);
            static::assertSame('image/png', $mimeType);
        }
    }

    public function testImageReplacement(): void
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewProduct();
        $product = $this->resource->create($data);

        $createdIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :productId',
            [
                ':productId' => $product->getId(),
            ]
        );

        $data = [
            '__options_images' => ['replace' => true],
            'images' => $this->getImagesForNewProduct(100),
        ];

        $this->resource->update($product->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :productId',
            [
                ':productId' => $product->getId(),
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
        $data['images'] = $this->getImagesForNewProduct();
        $product = $this->resource->create($data);

        $data = [
            '__options_images' => ['replace' => false],
            'images' => $this->getImagesForNewProduct(40),
        ];

        $this->resource->update($product->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :productId',
            [
                ':productId' => $product->getId(),
            ]
        );

        static::assertCount(10, $updateIds);
    }

    public function testImageReplacementWithoutOption(): void
    {
        $data = $this->getSimpleTestData();
        $data['images'] = $this->getImagesForNewProduct();
        $product = $this->resource->create($data);

        $data = [
            'images' => $this->getImagesForNewProduct(40),
        ];

        $this->resource->update($product->getId(), $data);

        $updateIds = Shopware()->Db()->fetchCol(
            'SELECT id FROM s_articles_img WHERE articleID = :productId',
            [
                ':productId' => $product->getId(),
            ]
        );

        static::assertCount(10, $updateIds);
    }

    public function testImageAttributes(): void
    {
        $data = $this->getSimpleTestData();
        $images = $this->getImagesForNewProduct();
        foreach ($images as &$image) {
            $image['attribute'] = [
                'attribute1' => 'attr1',
            ];
        }
        unset($image);
        $data['images'] = $images;
        foreach ($this->resource->create($data)->getImages() as $image) {
            static::assertInstanceOf(ProductImage::class, $image->getAttribute());
            static::assertSame('attr1', $image->getAttribute()->getAttribute1());
            static::assertNull($image->getAttribute()->getAttribute2());
            static::assertNull($image->getAttribute()->getAttribute3());
        }
    }

    public function testCreateWithDuplicateProperties(): void
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['values', 'option'])
            ->from(Value::class, 'values')
            ->innerJoin('values.option', 'option')
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = [];
        $valueIds = [];
        $optionIds = [];
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
        $product = $this->resource->create($data);
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $product = $this->resource->getOne($product->getId());
        static::assertIsArray($product);
        foreach ($product['propertyValues'] as $value) {
            static::assertContains($value['id'], $valueIds);
            static::assertContains($value['optionId'], $optionIds);
        }
    }

    public function testCreateWithMultiplePropertiesAndNewGroup(): void
    {
        $data = $this->getSimpleTestData();

        $optionName = 'newOption' . uniqid((string) mt_rand());
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
        $product = $this->resource->create($data);
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $productId = $product->getId();
        $product = $this->resource->getOne($productId);
        static::assertIsArray($product);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['option'])
            ->from(PropertyOption::class, 'option')
            ->where('option.name = :optionName')
            ->setParameter('optionName', $optionName)
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        static::assertSame($product['propertyValues'][0]['optionId'], $product['propertyValues'][1]['optionId']);
        static::assertCount(1, $databaseValuesOptions);

        $this->resource->delete($productId);

        // delete test values in s_filter_values
        $sql = 'DELETE FROM `s_filter_values` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        // delete test values in s_filter_relations
        $sql = 'DELETE FROM `s_filter_relations` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        // delete test values in s_filter_options
        $builder->delete(PropertyOption::class, 'option')->andWhere(
            'option.name = :optionName'
        )->setParameter('optionName', $optionName)->getQuery()->execute();
    }

    public function testUpdateWithDuplicateProperties(): void
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['values', 'option'])
            ->from(Value::class, 'values')
            ->innerJoin('values.option', 'option')
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValues = $builder->getQuery()->getArrayResult();
        $properties = [];
        $valueIds = [];
        $optionIds = [];
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
        $product = $this->resource->create($data);
        $product = $this->resource->update($product->getId(), $update);
        foreach ($product->getPropertyValues() as $value) {
            static::assertContains($value->getId(), $valueIds);
            static::assertContains($value->getOption()->getId(), $optionIds);
        }
    }

    public function testPriceReplacement(): void
    {
        $data = $this->getSimpleTestData();
        $product = $this->resource->create($data);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());

        $update = [
            'mainDetail' => [
                'number' => $product->getMainDetail()->getNumber(),
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

        $product = $this->resource->update($product->getId(), $update);
        static::assertInstanceOf(ProductModel::class, $product);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());
        static::assertCount(3, $product->getMainDetail()->getPrices());
    }

    public function testUpdateWithMultiplePropertiesAndNewGroup(): void
    {
        $optionName = 'newOption' . uniqid((string) mt_rand());
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
        $product = $this->resource->create($data);
        $product = $this->resource->update($product->getId(), $update);

        $productId = $product->getId();
        $this->resource->setResultMode(
            Resource::HYDRATE_ARRAY
        );
        $product = $this->resource->getOne($product->getId());
        static::assertIsArray($product);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['option'])
            ->from(PropertyOption::class, 'option')
            ->where('option.name = :optionName')
            ->setParameter('optionName', $optionName)
            ->setFirstResult(0)
            ->setMaxResults(20);
        $databaseValuesOptions = $builder->getQuery()->getArrayResult();

        static::assertSame($product['propertyValues'][0]['optionId'], $product['propertyValues'][1]['optionId']);
        static::assertCount(1, $databaseValuesOptions);

        $this->resource->delete($productId);

        // delete test values in s_filter_values
        $sql = 'DELETE FROM `s_filter_values` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        // delete test values in s_filter_relations
        $sql = 'DELETE FROM `s_filter_relations` WHERE `optionId` = ?';
        Shopware()->Db()->query($sql, [$databaseValuesOptions[0]['id']]);

        // delete test values in s_filter_options
        $builder->delete(PropertyOption::class, 'option')->andWhere(
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

        $product = $this->resource->create($create);

        foreach ($product->getImages() as $image) {
            static::assertCount(1, $image->getMappings());

            foreach ($image->getMappings() as $mapping) {
                static::assertCount(1, $mapping->getRules());
            }
        }

        $this->resource->generateVariantImages($product);

        $product = $this->resource->getOne($product->getId());
        static::assertInstanceOf(ProductModel::class, $product);

        foreach ($product->getDetails() as $variant) {
            foreach ($variant->getConfiguratorOptions() as $option) {
                if ($option->getName() === $usedOption[0]['name']) {
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

    public function testProductDefaultPriceBehavior(): void
    {
        $data = $this->getSimpleTestData();

        $product = $this->resource->create($data);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());

        $price = $product->getMainDetail()->getPrices()->first();
        static::assertInstanceOf(Price::class, $price);

        static::assertInstanceOf(Tax::class, $product->getTax());
        static::assertSame(
            400 / (((float) $product->getTax()->getTax() + 100) / 100),
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $data = $this->resource->getOne($product->getId());
        static::assertIsArray($data);

        static::assertEqualsWithDelta(
            400 / (((float) $product->getTax()->getTax() + 100) / 100),
            $data['mainDetail']['prices'][0]['price'],
            Utils::FORMER_PHPUNIT_FLOAT_EPSILON
        );
    }

    public function testSimilarWithNumber(): void
    {
        $products = $this->getEntityOffset(ProductModel::class, 0, 3);

        $data = $this->getSimpleTestData();
        $similar = [];
        foreach ($products as $product) {
            $model = Shopware()->Models()->find(ProductModel::class, $product['id']);
            static::assertInstanceOf(ProductModel::class, $model);
            static::assertInstanceOf(ProductVariant::class, $model->getMainDetail());

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
            static::assertInstanceOf(ProductModel::class, $model);
            static::assertInstanceOf(ProductVariant::class, $model->getMainDetail());

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
            ['link' => 'file://' . __DIR__ . '/fixtures/shopware_logo.png'],
        ];

        $product = $this->resource->create($data);

        static::assertCount(1, $product->getDownloads());

        static::assertInstanceOf(Download::class, $product->getDownloads()->first());
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
            static::assertInstanceOf(Shop::class, $category->getShop());
            static::assertContains($category->getShop()->getId(), [1, 2]);
        }
    }

    public function testProductGrossPrices(): void
    {
        $data = $this->getSimpleTestData();

        $product = $this->resource->create($data);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertInstanceOf(ProductVariant::class, $product->getMainDetail());

        $price = $product->getMainDetail()->getPrices()->first();
        static::assertInstanceOf(Price::class, $price);

        static::assertInstanceOf(Tax::class, $product->getTax());
        $net = 400 / (((float) $product->getTax()->getTax() + 100) / 100);

        static::assertSame(
            $net,
            $price->getPrice(),
            'Customer group price not calculated'
        );

        $this->resource->setResultMode(2);

        $data = $this->resource->getOne(
            $product->getId(),
            [
                'considerTaxInput' => true,
            ]
        );
        static::assertIsArray($data);

        $price = $data['mainDetail']['prices'][0];

        static::assertEqualsWithDelta(400, $price['price'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
        static::assertEqualsWithDelta($net, $price['net'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
        static::assertEqualsWithDelta(450, $price['pseudoPrice'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
        static::assertEqualsWithDelta(400, $price['regulationPrice'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
    }

    public function testAssignCategoriesByPathShouldBeSuccessful(): void
    {
        // Associate three kinds of categories with the product:
        // category by id, category by path, new category by path
        $product = $this->resource->create(
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
        $ids = array_flip(array_map(
            static function (Category $category) {
                return $category->getId();
            },
            $product->getCategories()->toArray()
        ));
        static::assertArrayHasKey(12, $ids);
        static::assertArrayHasKey(16, $ids);
        static::assertCount(3, $ids);

        $this->resource->delete($product->getId());
    }

    public function testBatchModeShouldBeSuccessful(): void
    {
        $createNew = $this->getSimpleTestData();
        $createNew['translations'] = [
            2 => [
                'name' => 'test product',
                'shopId' => 2,
            ],
        ];
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

        static::assertSame('newKeyword1', $result['existingByNumber']['data']['keywords']);
        static::assertSame('newKeyword2', $result['existingById']['data']['keywords']);
        static::assertSame('Testartikel', $result['new']['data']['name']);
        $newData = $this->resource->getOne($result['new']['data']['id']);
        static::assertIsArray($newData);
        static::assertSame('test product', $newData['translations'][2]['name']);
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
        $number = 'CategoryAssignment' . uniqid((string) mt_rand());

        $data = $this->getSimpleTestData();
        $data['mainDetail']['number'] = $number;

        $categories = Shopware()->Db()->fetchAll('SELECT id FROM s_categories WHERE parent = 3 ORDER BY id LIMIT 2');
        $data['categories'] = $categories;

        $product = $this->resource->create($data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$product->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$product->getId()]
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

        $this->resource->update($product->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$product->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$product->getId()]
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
        $this->resource->update($product->getId(), $data);

        $normal = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories WHERE articleID = ?',
            [$product->getId()]
        );

        $denormalized = Shopware()->Db()->fetchCol(
            'SELECT categoryID FROM s_articles_categories_ro WHERE articleID = ?',
            [$product->getId()]
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

    public function testVariantImagesOnProductCreate(): void
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
                'number' => uniqid((string) mt_rand()),
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
                    'number' => uniqid((string) mt_rand()) . '.1',
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
            static::assertInstanceOf(Media::class, $image->getMedia());
            $media = $image->getMedia();

            static::assertCount(1, $image->getMappings());

            $mappingValues = $image->getMappings()->getValues();
            $mapping = array_shift($mappingValues);

            static::assertCount(1, $mapping->getRules());

            $ruleValues = $mapping->getRules()->getValues();
            $rule = array_shift($ruleValues);

            if ($media->getId() === 2) {
                static::assertSame('NewVal1', $rule->getOption()->getName());
            } elseif ($media->getId() === 3) {
                static::assertSame('Newval2', $rule->getOption()->getName());
            }
        }
    }

    public function testProductCreateWithNoStock(): void
    {
        $data = [
            'name' => 'product without stock definition',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'withoutstock' . uniqid((string) mt_rand()),
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'price' => 999,
                    ],
                ],
            ],
        ];

        $model = $this->resource->create($data);
        static::assertInstanceOf(ProductModel::class, $model);
        static::assertInstanceOf(ProductVariant::class, $model->getMainDetail());
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
            static::assertInstanceOf(ProductModel::class, $temp);
            static::assertInstanceOf(ProductVariant::class, $temp->getMainDetail());

            static::assertSame($val, $temp->getMainDetail()->getLastStock());
        }

        $this->resource->delete($id);
    }

    public function testDeletingProductAlsoDeletesDetailsAndAttributes(): void
    {
        $entityManager = Shopware()->Models();
        $productRepository = $entityManager->getRepository(ProductModel::class);
        $detailRepository = $entityManager->getRepository(ProductVariant::class);
        $attributeRepository = $entityManager->getRepository(ProductAttribute::class);

        // Test preparation

        /* Create a product with one main detail and two additional details
         * (variants). */
        $testData = $this->getSimpleTestData();
        $configurator = $this->getSimpleConfiguratorSet(1, 2);
        $variants = array_map(
            // Add attribute values to each variant.
            /* While the values are not relevant to the test, and attributes
             * would still be created without them, this test should still work
             * should the logic around when to create attributes change. */
            static function (array $variant) {
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

        /* Save the product, so that we can later test its removal and the
         * removal of its details and attributes. */
        $createdProductId = $this->resource->create($testData)->getId();

        // Retrieve identifiers from the database which we need for this test.
        try {
            $createdProduct = $productRepository->find($createdProductId);
            static::assertInstanceOf(ProductModel::class, $createdProduct);
            $createdDetails = array_merge(
                $createdProduct->getDetails()->toArray(),
                [$createdProduct->getMainDetail()]
            );

            /* The identifiers of the details the removal of which we will
             * later verify. */
            $createdDetailIds = array_unique(array_map(
                static function (ProductVariant $createdDetail) {
                    return $createdDetail->getId();
                },
                $createdDetails
            ));

            $createdAttributes = array_map(static function (ProductVariant $createdDetail) {
                self::assertInstanceOf(ProductAttribute::class, $createdDetail->getAttribute());

                return $createdDetail->getAttribute();
            },
                $createdDetails
            );
            // Verify we did not break our test setup.
            static::assertCount(3, $createdDetailIds);

            /* The identifiers of the attributes the removal of which we will
             * later verify. */
            $createdAttributeIds = array_unique(array_map(
                static function (ProductAttribute $createdAttribute) {
                    return $createdAttribute->getId();
                },
                $createdAttributes
            ));
            static::assertCount(3, $createdAttributeIds);

            // Verify created product's details match test setup assumptions.
            static::assertCount(\count($createdDetailIds), $detailRepository->findBy([
                'id' => $createdDetailIds,
            ]));
            // Verify created product's attributes match test setup assumptions.
            static::assertCount(\count($createdAttributeIds), $attributeRepository->findBy([
                'id' => $createdAttributeIds,
            ]));
        } finally {
            // Even if the test setup fails, delete the test's created product.

            // Tested action:
            $this->resource->delete($createdProductId);
        }

        static::assertNull(
            $productRepository->find($createdProductId),
            sprintf(
                'Deletion of the product (id = %s) itself failed.',
                $createdProductId
            )
        );

        // Test assertions
        static::assertCount(
            0,
            $detailRepository->findBy([
                'id' => $createdDetailIds,
            ]),
            sprintf(
                'Deletion of the product\'s (id = %s) details (%s) failed.',
                $createdProductId,
                implode(', ', $createdDetailIds)
            )
        );
        static::assertCount(
            0,
            $attributeRepository->findBy(['id' => $createdAttributeIds]),
            sprintf(
                "Deletion of the product's (id = %s) details' (%s) attributes (%s) failed.",
                $createdProductId,
                implode(', ', $createdDetailIds),
                implode(', ', $createdAttributeIds)
            )
        );
    }

    /**
     * @group pcovAdapterBrokenTest
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function testInvalidProductInBatch(): void
    {
        $minimalTestProduct = [
            'id' => 2,
            'images' => [
                [
                    'mediaId' => 10,
                    'description' => null,
                ],
            ],
        ];

        $result = $this->resource->batch([$minimalTestProduct, $minimalTestProduct]);

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
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand()),
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

        $product = $this->createProduct($testData);

        $countOfPrices = (int) Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_articles_prices');

        $testData['__options_variants']['replace'] = true;

        $this->resource->update($product->getId(), $testData);

        $currentCountOfPrices = (int) Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM s_articles_prices');

        static::assertSame($countOfPrices, $currentCountOfPrices);
        $this->resource->delete($product->getId());
    }

    /**
     * Combinations merge the result of dimensional arrays not perfectly,
     * so we have to clean up the first array level.
     *
     * @param list<array{groupId: int, option: string}> $combinations
     *
     * @return list<array{groupId: int, option: string}>
     */
    private function cleanUpCombinations(array $combinations): array
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
     * @param class-string<ModelEntity> $entity
     */
    private function internalTestReplaceMode(string $entity, string $arrayKey, bool $replace = true): void
    {
        // Create keys for getter function and the __options parameter in the update and create
        // Example => "__options_categories"  /  "getCategories"
        $replaceKey = '__options_' . $arrayKey;
        $getter = 'get' . ucfirst($arrayKey);

        // Returns a simple product data set to create a product with a simple main detail
        $data = $this->getSimpleTestData();

        // Get an offset of 10 entities for the current entity type, like 10x categories
        $createdEntities = $this->getEntityOffset($entity);
        $data[$arrayKey] = $createdEntities;

        $product = $this->resource->create($data);
        static::assertCount(\count($createdEntities), $product->$getter());

        $updatedEntity = $this->getEntityOffset($entity, 20, 5);

        $update = [
            $replaceKey => ['replace' => $replace],
            $arrayKey => $updatedEntity,
        ];
        $product = $this->resource->update($product->getId(), $update);

        if ($replace) {
            static::assertCount(\count($updatedEntity), $product->$getter());
        } else {
            static::assertCount(\count($createdEntities) + \count($updatedEntity), $product->$getter());
        }
    }

    /**
     * @param array{name: string, groups: array<array<string, mixed>>} $configuratorSet
     *
     * @return list<array<string, mixed>>
     */
    private function getOptionsForImage(array $configuratorSet, ?int $optionCount = null, string $property = 'id'): array
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
            if (\count($options) === $optionCount) {
                return $options;
            }
        }

        return $options;
    }

    /**
     * Helper function which creates all variants for the passed groups with options.
     *
     * @param array<array<string, mixed>> $groups
     *
     * @return list<array<string, mixed>>
     */
    private function createConfiguratorVariants(array $groups): array
    {
        $options = [];

        foreach ($groups as $group) {
            $groupOptions = [];
            foreach ($group['options'] as $option) {
                $groupOptions[] = [
                    'groupId' => (int) $group['id'],
                    'option' => (string) $option['name'],
                ];
            }
            $options[] = $groupOptions;
        }

        $combinations = $this->cleanUpCombinations($options[0]);

        $variants = [];
        foreach ($combinations as $combination) {
            $variant = $this->getSimpleVariantData();
            $variant['configuratorOptions'] = $combination;
            $variants[] = $variant;
        }

        return $variants;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getImagesForNewProduct(int $offset = 10): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select([
            'media.id as mediaId',
            '2 as main',
        ])
            ->from(Media::class, 'media', 'media.id')
            ->addOrderBy('media.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(5);

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
                'number' => 'swTEST' . uniqid((string) mt_rand()),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 400,
                        'pseudoPrice' => 450,
                        'regulationPrice' => 400,
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
        ];
    }

    /**
     * @param class-string<ModelEntity> $entity
     * @param list<string>              $fields
     *
     * @return array<array<string, mixed>>
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
        $builder->select($selectFields)
            ->from($entity, 'alias')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @return array{name: string, groups: array<array<string, mixed>>}
     */
    private function getSimpleConfiguratorSet(int $groupLimit = 3, int $optionLimit = 5): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['groups.id', 'groups.name'])
            ->from(ConfiguratorGroup::class, 'groups')
            ->setFirstResult(0)
            ->setMaxResults($groupLimit)
            ->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['options.id', 'options.name'])
            ->from(ConfiguratorOption::class, 'options')
            ->where('options.groupId = :groupId')
            ->setFirstResult(0)
            ->setMaxResults($optionLimit)
            ->orderBy('options.position', 'ASC');

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
            'number' => 'swTEST' . uniqid((string) mt_rand()),
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
     * @param array{name: string, groups: array<array<string, mixed>>} $configuratorSet
     *
     * @return list<array{optionId: int, groupId: int}>
     */
    private function getVariantOptionsOfSet(array $configuratorSet): array
    {
        $options = [];
        foreach ($configuratorSet['groups'] as $group) {
            $id = rand(0, \count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = [
                'optionId' => (int) $option['id'],
                'groupId' => (int) $group['id'],
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

    /**
     * @param array<string, mixed> $testData
     */
    private function createProduct(array $testData): ProductModel
    {
        $product = $this->resource->create($testData);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertGreaterThan(0, $product->getId());

        return $product;
    }

    /**
     * @return array<string, mixed>
     */
    private function getTestData(): array
    {
        return [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',
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
                        'name' => 'neueOption' . uniqid((string) mt_rand()),
                    ],
                ],
            ],
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) mt_rand()),
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
                        'name' => 'Größe',
                        'options' => [
                            ['name' => 'L'],
                            ['name' => 'XL'],
                        ],
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'swTEST.variant.' . uniqid((string) mt_rand()),
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
    }
}
