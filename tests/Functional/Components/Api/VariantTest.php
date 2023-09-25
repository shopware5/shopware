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

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Article as ProductResource;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Media\Media;
use Shopware\Models\Tax\Tax;
use Shopware\Tests\Functional\Helper\Utils;

class VariantTest extends TestCase
{
    /**
     * @var Variant
     */
    protected $resource;

    private ProductResource $productResource;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->productResource = new ProductResource();
        $this->productResource->setAcl(Shopware()->Acl());
        $this->productResource->setManager(Shopware()->Models());
    }

    /**
     * @return Variant
     */
    public function createResource()
    {
        return new Variant();
    }

    // Creates a product with variants
    public function testCreateShouldBeSuccessful(): ProductModel
    {
        // required field name is missing
        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',

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
                            ['name' => 'Grün'],
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
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    'unitId' => 1,

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
                    'number' => 'swTEST.variant.' . uniqid((string) rand()),
                    'inStock' => 17,
                    'unitId' => 1,

                    'attribute' => [
                        'attr3' => 'Freitext3',
                        'attr4' => 'Freitext4',
                    ],

                    'configuratorOptions' => [
                        [
                            'option' => 'Grün',
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

        $product = $this->productResource->create($testData);

        static::assertInstanceOf(ProductModel::class, $product);
        static::assertGreaterThan(0, $product->getId());

        static::assertEquals($product->getName(), $testData['name']);
        static::assertEquals($product->getDescription(), $testData['description']);

        static::assertEquals($product->getDescriptionLong(), $testData['descriptionLong']);
        $mainVariant = $product->getMainDetail();
        static::assertInstanceOf(Detail::class, $mainVariant);
        $attribute = $mainVariant->getAttribute();
        static::assertInstanceOf(ProductAttribute::class, $attribute);
        static::assertEquals($attribute->getAttr1(), $testData['mainDetail']['attribute']['attr1']);
        static::assertEquals($attribute->getAttr2(), $testData['mainDetail']['attribute']['attr2']);

        static::assertInstanceOf(Tax::class, $product->getTax());
        static::assertEquals($testData['taxId'], $product->getTax()->getId());

        static::assertCount(2, $mainVariant->getPrices());

        return $product;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testCreateWithExistingOrderNumberShouldThrowCustomValidationException(ProductModel $product): void
    {
        $this->expectException(CustomValidationException::class);
        static::assertInstanceOf(Detail::class, $product->getMainDetail());
        $testData = [
            'articleId' => $product->getId(),
            'number' => $product->getMainDetail()->getNumber(),
            'prices' => [
                [
                    'customerGroupKey' => 'EK',
                    'price' => 100,
                ],
            ],
        ];

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful(ProductModel $product): ProductModel
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        foreach ($product->getDetails() as $variant) {
            $variantById = $this->resource->getOne($variant->getId());
            static::assertInstanceOf(Detail::class, $variantById);
            $variantByNumber = $this->resource->getOneByNumber($variant->getNumber());
            static::assertInstanceOf(Detail::class, $variantByNumber);

            static::assertEquals($variant->getId(), $variantById->getId());
            static::assertEquals($variant->getId(), $variantByNumber->getId());
        }

        return $product;
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
     * @depends testGetOneShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful(ProductModel $product): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        $deleteByNumber = true;

        foreach ($product->getDetails() as $variant) {
            $deleteByNumber = !$deleteByNumber;

            if ($deleteByNumber) {
                $result = $this->resource->delete($variant->getId());
            } else {
                $result = $this->resource->deleteByNumber($variant->getNumber());
            }
            static::assertInstanceOf(Detail::class, $result);
            static::assertSame(0, (int) $result->getId());
        }

        // Delete the whole product at last
        $this->productResource->delete($product->getId());
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

    public function testVariantCreate(): int
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $product = $this->productResource->create($data);
        static::assertCount(0, $product->getDetails());

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);
        static::assertCount(\count($create['configuratorOptions']), $variant->getConfiguratorOptions());

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $variant = $this->resource->create($create);
        static::assertCount(\count($create['configuratorOptions']), $variant->getConfiguratorOptions());

        $this->productResource->setResultMode(Resource::HYDRATE_ARRAY);
        $id = $product->getId();
        $product = $this->productResource->getOne($id);
        static::assertIsArray($product);
        static::assertCount(2, $product['details']);

        return $id;
    }

    /**
     * @depends testVariantCreate
     */
    public function testVariantUpdate(int $productId): void
    {
        $this->productResource->setResultMode(Resource::HYDRATE_ARRAY);
        $product = $this->productResource->getOne($productId);
        static::assertIsArray($product);

        foreach ($product['details'] as $variantData) {
            $updateData = [
                'articleId' => $productId,
                'inStock' => 2000,
                'number' => $variantData['number'] . '-Updated',
                'unitId' => $this->getRandomId(),
                // Make sure conf. options and groups work in a case-insensitive way, just like in the DB
                'configuratorOptions' => [[
                    'group' => 'farbe',
                    'option' => 'Grün',
                ], [
                    'group' => 'Größe',
                    'option' => 'xl',
                ]],
            ];
            $variant = $this->resource->update($variantData['id'], $updateData);

            static::assertInstanceOf(Unit::class, $variant->getUnit());
            static::assertEquals($variant->getUnit()->getId(), $updateData['unitId']);
            static::assertEquals($variant->getInStock(), $updateData['inStock']);
            static::assertEquals($variant->getNumber(), $updateData['number']);
        }
    }

    public function testVariantImageAssignByMediaId(): int
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;
        $data['images'] = $this->getSimpleMedia(2);

        $product = $this->productResource->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $create['images'] = $this->getSimpleMedia(1);

        $variant = $this->resource->create($create);

        static::assertCount(1, $variant->getImages());

        return $variant->getId();
    }

    /**
     * @depends testVariantImageAssignByMediaId
     */
    public function testVariantImageReset(int $variantId): int
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $variant = $this->resource->getOne($variantId);
        static::assertInstanceOf(Detail::class, $variant);
        static::assertTrue($variant->getImages()->count() > 0);

        $update = [
            'articleId' => $variant->getArticle()->getId(),
            'images' => [],
        ];

        $variant = $this->resource->update($variantId, $update);

        static::assertCount(0, $variant->getImages());

        foreach ($variant->getArticle()->getImages() as $image) {
            static::assertCount(0, $image->getMappings());
        }

        return $variant->getId();
    }

    /**
     * @depends testVariantImageReset
     */
    public function testVariantAddImage(int $variantId): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $variant = $this->resource->getOne($variantId);
        static::assertInstanceOf(Detail::class, $variant);
        static::assertCount(0, $variant->getImages());

        $update = [
            'articleId' => $variant->getArticle()->getId(),
            'images' => $this->getSimpleMedia(3),
        ];
        $variant = $this->resource->update($variantId, $update);
        static::assertInstanceOf(Detail::class, $variant);
        static::assertCount(3, $variant->getImages());

        $add = [
            'articleId' => $variant->getArticle()->getId(),
            '__options_images' => ['replace' => false],
            'images' => $this->getSimpleMedia(5, 20),
        ];
        $variant = $this->resource->update($variantId, $add);
        static::assertInstanceOf(Detail::class, $variant);
        static::assertCount(8, $variant->getImages());

        foreach ($variant->getArticle()->getImages() as $image) {
            static::assertCount(1, $image->getMappings(), 'No image mapping created!');

            $mapping = $image->getMappings()->current();
            static::assertCount(
                $variant->getConfiguratorOptions()->count(),
                $mapping->getRules(),
                'Image mapping contains not enough rules. '
            );
        }
    }

    public function testVariantImageCreateByLink(): int
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;
        $product = $this->productResource->create($data);
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $create['images'] = [
            ['link' => 'data:image/png;base64,' . require (__DIR__ . '/fixtures/base64image.php')],
            ['link' => 'file://' . __DIR__ . '/fixtures/variant-image.png'],
        ];

        $this->productResource->setResultMode(Resource::HYDRATE_OBJECT);
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        $variant = $this->resource->create($create);
        static::assertInstanceOf(Detail::class, $variant);
        $product = $this->productResource->getOne($product->getId());
        static::assertInstanceOf(ProductModel::class, $product);

        static::assertCount(2, $product->getImages());

        foreach ($product->getImages() as $image) {
            $media = null;
            while ($media === null) {
                if ($image->getMedia()) {
                    $media = $image->getMedia();
                } elseif ($image->getParent()) {
                    $image = $image->getParent();
                } else {
                    break;
                }
            }

            static::assertInstanceOf(Media::class, $media);
            static::assertCount(4, $media->getThumbnails());
            foreach ($media->getThumbnails() as $thumbnail) {
                static::assertTrue($mediaService->has(Shopware()->DocPath() . $thumbnail));
            }

            static::assertCount(1, $image->getMappings(), 'No image mapping created!');

            $mapping = $image->getMappings()->current();
            static::assertCount(
                $variant->getConfiguratorOptions()->count(),
                $mapping->getRules(),
                'Image mapping does not contain enough rules.'
            );
        }

        return $variant->getId();
    }

    public function testVariantDefaultPriceBehavior(): void
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();

        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $product = $this->productResource->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);

        $this->resource->setResultMode(2);
        $data = $this->resource->getOne($variant->getId());
        static::assertIsArray($data);

        static::assertEqualsWithDelta(400 / 1.19, $data['prices'][0]['price'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
    }

    public function testVariantGrossPrices(): void
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();

        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $product = $this->productResource->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $product->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);

        $this->resource->setResultMode(2);
        $data = $this->resource->getOne($variant->getId(), [
            'considerTaxInput' => true,
        ]);
        static::assertIsArray($data);

        static::assertEqualsWithDelta(400, $data['prices'][0]['price'], Utils::FORMER_PHPUNIT_FLOAT_EPSILON);
    }

    public function testBatchModeShouldBeSuccessful(): void
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $product = $this->productResource->create($data);
        static::assertCount(0, $product->getDetails());

        // Create 5 new variants
        $batchData = [];
        for ($i = 0; $i < 5; ++$i) {
            $create = $this->getSimpleVariantData();
            $create['articleId'] = $product->getId();
            $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
            $batchData[] = $create;
        }

        // Update the price of the existing variant
        $existingVariant = $data['mainDetail'];
        $existingVariant['prices'] = [
            [
                'customerGroupKey' => 'EK',
                'from' => 1,
                'to' => '-',
                'price' => 473.99,
            ],
        ];
        $batchData[] = $existingVariant;

        // Run batch operations
        $this->resource->batch($batchData);

        // Check results
        $this->productResource->setResultMode(Resource::HYDRATE_ARRAY);
        $id = $product->getId();
        $product = $this->productResource->getOne($id);
        static::assertIsArray($product);

        static::assertCount(5, $product['details']);
        static::assertEquals(398, round($product['mainDetail']['prices'][0]['price']));
    }

    public function testNewConfiguratorOptionForVariant(): void
    {
        $data = $this->getSimpleProductData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet(1, 2);
        $data['configuratorSet'] = $configuratorSet;

        $product = $this->productResource->create($data);

        // Create 5 new variants
        $batchData = [];
        $names = [];
        for ($i = 0; $i < 5; ++$i) {
            $create = $this->getSimpleVariantData();
            $create['articleId'] = $product->getId();

            $options = $this->getVariantOptionsOfSet($configuratorSet);

            unset($options[0]['optionId']);
            $name = 'New-' . uniqid((string) rand());
            $names[] = $name;
            $options[0]['option'] = $name;
            $create['configuratorOptions'] = $options;

            $batchData[] = $create;
        }

        // Run batch operations
        $result = $this->resource->batch($batchData);

        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        foreach ($result as $operation) {
            static::assertTrue($operation['success']);

            $variant = $this->resource->getOne($operation['data']['id']);
            static::assertIsArray($variant);

            static::assertCount(1, $variant['configuratorOptions']);

            $option = $variant['configuratorOptions'][0];

            static::assertContains($option['name'], $names);
        }
    }

    public function testCreateConfiguratorOptionsWithPosition(): void
    {
        // required field name is missing
        $testData = [
            'name' => 'Testartikel',
            'taxId' => 1,
            'supplierId' => 2,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => 20,
                        'price' => 500,
                    ],
                ],
            ],
            'configuratorSet' => [
                'name' => 'CreateOptionsWithPosition',
                'groups' => [
                    [
                        'name' => 'First group',
                        'options' => [
                            ['name' => 'group with 10', 'position' => 10],
                            ['name' => 'group with 5', 'position' => 5],
                        ],
                    ],
                    [
                        'name' => 'Second group',
                        'options' => [
                            ['name' => 'group with 30', 'position' => 30],
                            ['name' => 'group with 12', 'position' => 12],
                        ],
                    ],
                ],
            ],
        ];

        $product = $this->productResource->create($testData);
        static::assertInstanceOf(ProductModel::class, $product);
        static::assertGreaterThan(0, $product->getId());

        static::assertInstanceOf(Set::class, $product->getConfiguratorSet());
        $groups = $product->getConfiguratorSet()->getGroups();
        static::assertCount(2, $groups);

        foreach ($groups as $group) {
            static::assertCount(2, $group->getOptions());
            foreach ($group->getOptions() as $option) {
                switch ($option->getName()) {
                    case 'group with 10':
                        static::assertEquals(10, $option->getPosition());
                        break;
                    case 'group with 5':
                        static::assertEquals(5, $option->getPosition());
                        break;
                    case 'group with 30':
                        static::assertEquals(30, $option->getPosition());
                        break;
                    case 'group with 12':
                        static::assertEquals(12, $option->getPosition());
                        break;
                }
            }
        }
    }

    public function testCreateEsdVariant(): void
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand1' . uniqid((string) rand()),
                'inStock' => 15,
                'active' => true,

                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'price' => 50,
                    ],
                ],
                'esd' => [
                    'file' => 'file://' . __DIR__ . '/fixtures/shopware_logo.png',
                    'reuse' => true,
                ],
            ],
        ];

        $esdDir = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

        if (!is_writable($esdDir)) {
            $this->expectExceptionMessageMatches('/Unable to save ESD-file, as the directory ".*" is not writable/');
        }

        $product = $this->productResource->create($params);

        if (!is_writable($esdDir)) {
            static::markTestIncomplete('Skipping thorough ESD-file-check, as the process is unable to read/write to the file-directory.');
        }

        $mainVariant = $product->getMainDetail();
        static::assertInstanceOf(Detail::class, $mainVariant);
        static::assertInstanceOf(Esd::class, $mainVariant->getEsd());
        static::assertEquals('shopware_logo.png', $mainVariant->getEsd()->getFile());
    }

    public function testCreateEsdWithSerialsVariant(): void
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand2' . uniqid((string) rand()),
                'inStock' => 15,
                'active' => true,

                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'price' => 50,
                    ],
                ],
                'esd' => [
                    'file' => 'file://' . __DIR__ . '/fixtures/shopware_logo.png',
                    'reuse' => true,
                    'hasSerials' => true,
                    'serials' => [
                        [
                            'serialnumber' => '1000',
                        ],
                        [
                            'serialnumber' => '1001',
                        ],
                        [
                            'serialnumber' => '1002',
                        ],
                        [
                            'serialnumber' => '1003',
                        ],
                        [
                            'serialnumber' => '1004',
                        ],
                    ],
                ],
            ],
        ];

        $esdDir = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

        if (!is_writable($esdDir)) {
            $this->expectExceptionMessageMatches('/Unable to save ESD-file, as the directory ".*" is not writable/');
        }

        $product = $this->productResource->create($params);

        if (!is_writable($esdDir)) {
            static::markTestIncomplete('Skipping thorough ESD-file-check, as the process is unable to read/write to the file-directory.');
        }

        $mainVariant = $product->getMainDetail();
        static::assertInstanceOf(Detail::class, $mainVariant);
        static::assertInstanceOf(Esd::class, $mainVariant->getEsd());
        static::assertEquals(5, $mainVariant->getEsd()->getSerials()->count());
        static::assertTrue($mainVariant->getEsd()->getHasSerials());
        static::assertEquals('shopware_logo.png', $mainVariant->getEsd()->getFile());
    }

    /**
     * @depends testCreateEsdVariant
     */
    public function testCreateEsdReuseVariant(): void
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand2' . uniqid((string) rand()),
                'inStock' => 15,
                'active' => true,

                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'price' => 50,
                    ],
                ],
                'esd' => [
                    'file' => 'file://' . __DIR__ . '/fixtures/shopware_logo.png',
                    'hasSerials' => true,
                    'reuse' => false,
                    'serials' => [
                        [
                            'serialnumber' => '1000',
                        ],
                        [
                            'serialnumber' => '1001',
                        ],
                        [
                            'serialnumber' => '1002',
                        ],
                        [
                            'serialnumber' => '1003',
                        ],
                        [
                            'serialnumber' => '1004',
                        ],
                    ],
                ],
            ],
        ];

        $esdDir = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

        if (!is_writable($esdDir)) {
            $this->expectExceptionMessageMatches('/Unable to save ESD-file, as the directory ".*" is not writable/');
        }

        $product = $this->productResource->create($params);

        if (!is_writable($esdDir)) {
            static::markTestIncomplete('Skipping thorough ESD-file-check, as the process is unable to read/write to the file-directory.');
        }

        $mainVariant = $product->getMainDetail();
        static::assertInstanceOf(Detail::class, $mainVariant);
        static::assertInstanceOf(Esd::class, $mainVariant->getEsd());
        static::assertEquals(5, $mainVariant->getEsd()->getSerials()->count());
        static::assertTrue($mainVariant->getEsd()->getHasSerials());
        static::assertNotEquals('shopware_logo.png', $mainVariant->getEsd()->getFile());
    }

    /**
     * @param array<string, mixed> $configuratorSet
     *
     * @return array<array{optionId: int, groupId: int}>
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

    /**
     * @return array<string, mixed>
     */
    private function getSimpleMedia(int $limit = 5, int $offset = 0): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('media.id  as mediaId')
            ->from(Media::class, 'media')
            ->where('media.albumId = -1')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    private function getRandomId(): int
    {
        return (int) Shopware()->Db()->fetchOne('SELECT id FROM s_core_units LIMIT 1');
    }

    /**
     * @return array{number: string, inStock: int, unitId: int, prices: array<array{customerGroupKey: string, from: int, to: string, price: float}>}
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
     * @return array{name: string, description: string, active: bool, taxId: int, supplierId: int}
     */
    private function getSimpleProductData(): array
    {
        return [
            'name' => 'Images - Test Artikel',
            'description' => 'Test description',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 2,
        ];
    }

    /**
     * @return array{name: string, groups: array<string, mixed>}
     */
    private function getSimpleConfiguratorSet(int $groupLimit = 3, int $optionLimit = 5): array
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['groups.id'])
            ->from(Group::class, 'groups')
            ->setFirstResult(0)
            ->setMaxResults($groupLimit)
            ->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['options.id'])
            ->from(Option::class, 'options')
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
}
