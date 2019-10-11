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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Esd;

class VariantTest extends TestCase
{
    /**
     * @var Variant
     */
    protected $resource;

    /**
     * @var Article
     */
    private $resourceArticle;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->resourceArticle = new Article();
        $this->resourceArticle->setAcl(Shopware()->Acl());
        $this->resourceArticle->setManager(Shopware()->Models());
    }

    /**
     * @return Variant
     */
    public function createResource()
    {
        return new Variant();
    }

    // Creates a article with variants
    public function testCreateShouldBeSuccessful()
    {
        // required field name is missing
        $testData = [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',

            'mainDetail' => [
                'number' => 'swTEST' . uniqid(rand()),
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
                    'number' => 'swTEST.variant.' . uniqid(rand()),
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
                    'number' => 'swTEST.variant.' . uniqid(rand()),
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

        $article = $this->resourceArticle->create($testData);

        static::assertInstanceOf('\Shopware\Models\Article\Article', $article);
        static::assertGreaterThan(0, $article->getId());

        static::assertEquals($article->getName(), $testData['name']);
        static::assertEquals($article->getDescription(), $testData['description']);

        static::assertEquals($article->getDescriptionLong(), $testData['descriptionLong']);
        static::assertEquals($article->getMainDetail()->getAttribute()->getAttr1(), $testData['mainDetail']['attribute']['attr1']);
        static::assertEquals($article->getMainDetail()->getAttribute()->getAttr2(), $testData['mainDetail']['attribute']['attr2']);

        static::assertEquals($testData['taxId'], $article->getTax()->getId());

        static::assertEquals(2, count($article->getMainDetail()->getPrices()));

        return $article;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testCreateWithExistingOrderNumberShouldThrowCustomValidationException(\Shopware\Models\Article\Article $article)
    {
        $this->expectException('Shopware\Components\Api\Exception\CustomValidationException');
        $testData = [
            'articleId' => $article->getId(),
            'number' => $article->getMainDetail()->getNumber(),
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
     *
     * @return \Shopware\Models\Article\Article
     */
    public function testGetOneShouldBeSuccessful(\Shopware\Models\Article\Article $article)
    {
        $this->resource->setResultMode(Variant::HYDRATE_OBJECT);

        /** @var \Shopware\Models\Article\Detail $articleDetail */
        foreach ($article->getDetails() as $articleDetail) {
            $articleDetailById = $this->resource->getOne($articleDetail->getId());
            $articleDetailByNumber = $this->resource->getOneByNumber($articleDetail->getNumber());

            static::assertEquals($articleDetail->getId(), $articleDetailById->getId());
            static::assertEquals($articleDetail->getId(), $articleDetailByNumber->getId());
        }

        return $article;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testGetOneShouldBeSuccessful
     *
     * @param \Shopware\Models\Article\Article $article
     */
    public function testDeleteShouldBeSuccessful($article)
    {
        $this->resource->setResultMode(Variant::HYDRATE_OBJECT);

        $deleteByNumber = true;

        /** @var \Shopware\Models\Article\Detail $articleDetail */
        foreach ($article->getDetails() as $articleDetail) {
            $deleteByNumber = !$deleteByNumber;

            if ($deleteByNumber) {
                $result = $this->resource->delete($articleDetail->getId());
            } else {
                $result = $this->resource->deleteByNumber($articleDetail->getNumber());
            }
            static::assertInstanceOf('\Shopware\Models\Article\Detail', $result);
            static::assertEquals(null, $result->getId());
        }

        // Delete the whole article at last
        $this->resourceArticle->delete($article->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete('');
    }

    public function testVariantCreate()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $article = $this->resourceArticle->create($data);
        static::assertCount(0, $article->getDetails());

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);
        static::assertCount(count($create['configuratorOptions']), $variant->getConfiguratorOptions());

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $variant = $this->resource->create($create);
        static::assertCount(count($create['configuratorOptions']), $variant->getConfiguratorOptions());

        $this->resourceArticle->setResultMode(Variant::HYDRATE_ARRAY);
        $id = $article->getId();
        $article = $this->resourceArticle->getOne($id);
        static::assertCount(2, $article['details']);

        return $id;
    }

    /**
     * @depends testVariantCreate
     *
     * @param int $articleId
     */
    public function testVariantUpdate($articleId)
    {
        $this->resourceArticle->setResultMode(Variant::HYDRATE_ARRAY);
        $article = $this->resourceArticle->getOne($articleId);

        foreach ($article['details'] as $variantData) {
            $updateData = [
                'articleId' => $articleId,
                'inStock' => 2000,
                'number' => $variantData['number'] . '-Updated',
                'unitId' => $this->getRandomId('s_core_units'),
                // Make sure conf. options and groups work in a case insensitive way, just like in the DB
                'configuratorOptions' => [[
                    'group' => 'farbe',
                    'option' => 'Grün',
                ], [
                    'group' => 'Gräße',
                    'option' => 'xl',
                ]],
            ];
            $variant = $this->resource->update($variantData['id'], $updateData);

            static::assertEquals($variant->getUnit()->getId(), $updateData['unitId']);
            static::assertEquals($variant->getInStock(), $updateData['inStock']);
            static::assertEquals($variant->getNumber(), $updateData['number']);
        }
    }

    public function testVariantImageAssignByMediaId()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;
        $data['images'] = $this->getSimpleMedia(2);

        $article = $this->resourceArticle->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $create['images'] = $this->getSimpleMedia(1);

        /** @var \Shopware\Models\Article\Detail $variant */
        $variant = $this->resource->create($create);

        static::assertCount(1, $variant->getImages());

        return $variant->getId();
    }

    /**
     * @depends testVariantImageAssignByMediaId
     *
     * @param int $variantId
     *
     * @return int
     */
    public function testVariantImageReset($variantId)
    {
        $this->resource->setResultMode(Variant::HYDRATE_OBJECT);
        $variant = $this->resource->getOne($variantId);
        static::assertTrue($variant->getImages()->count() > 0);

        $update = [
            'articleId' => $variant->getArticle()->getId(),
            'images' => [],
        ];

        $variant = $this->resource->update($variantId, $update);

        static::assertCount(0, $variant->getImages());

        $article = $variant->getArticle();
        /** @var \Shopware\Models\Article\Image $image */
        foreach ($article->getImages() as $image) {
            static::assertCount(0, $image->getMappings());
        }

        return $variant->getId();
    }

    /**
     * @depends testVariantImageReset
     *
     * @param int $variantId
     */
    public function testVariantAddImage($variantId)
    {
        $this->resource->setResultMode(Variant::HYDRATE_OBJECT);
        $variant = $this->resource->getOne($variantId);
        static::assertTrue($variant->getImages()->count() === 0);

        $update = [
            'articleId' => $variant->getArticle()->getId(),
            'images' => $this->getSimpleMedia(3),
        ];
        $variant = $this->resource->update($variantId, $update);
        static::assertCount(3, $variant->getImages());

        $add = [
            'articleId' => $variant->getArticle()->getId(),
            '__options_images' => ['replace' => false],
            'images' => $this->getSimpleMedia(5, 20),
        ];
        $variant = $this->resource->update($variantId, $add);
        static::assertCount(8, $variant->getImages());

        /** @var \Shopware\Models\Article\Image $image */
        foreach ($variant->getArticle()->getImages() as $image) {
            static::assertCount(1, $image->getMappings(), 'No image mapping created!');

            /** @var \Shopware\Models\Article\Image\Mapping $mapping */
            $mapping = $image->getMappings()->current();
            static::assertCount(
                $variant->getConfiguratorOptions()->count(),
                $mapping->getRules(),
                'Image mapping contains not enough rules. '
            );
        }
    }

    /**
     * @return int
     */
    public function testVariantImageCreateByLink()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;
        $article = $this->resourceArticle->create($data);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);
        $create['images'] = [
            ['link' => 'data:image/png;base64,' . require (__DIR__ . '/fixtures/base64image.php')],
            ['link' => 'file://' . __DIR__ . '/fixtures/variant-image.png'],
        ];

        $this->resourceArticle->setResultMode(Variant::HYDRATE_OBJECT);
        $this->resource->setResultMode(Variant::HYDRATE_OBJECT);

        /** @var \Shopware\Models\Article\Detail $variant */
        $variant = $this->resource->create($create);
        $article = $this->resourceArticle->getOne($article->getId());

        static::assertCount(2, $article->getImages());

        /** @var \Shopware\Models\Article\Image $image */
        foreach ($article->getImages() as $image) {
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

            static::assertCount(4, $media->getThumbnails());
            foreach ($media->getThumbnails() as $thumbnail) {
                static::assertTrue($mediaService->has(Shopware()->DocPath() . $thumbnail));
            }

            static::assertCount(1, $image->getMappings(), 'No image mapping created!');

            /** @var \Shopware\Models\Article\Image\Mapping $mapping */
            $mapping = $image->getMappings()->current();
            static::assertCount(
                $variant->getConfiguratorOptions()->count(),
                $mapping->getRules(),
                'Image mapping does not contain enough rules.'
            );
        }

        return $variant->getId();
    }

    public function testVariantDefaultPriceBehavior()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();

        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $article = $this->resourceArticle->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);

        $this->resource->setResultMode(2);
        $data = $this->resource->getOne($variant->getId());

        static::assertEquals(400 / 1.19, $data['prices'][0]['price']);
    }

    public function testVariantGrossPrices()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();

        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $article = $this->resourceArticle->create($data);

        $create = $this->getSimpleVariantData();
        $create['articleId'] = $article->getId();
        $create['configuratorOptions'] = $this->getVariantOptionsOfSet($configuratorSet);

        $variant = $this->resource->create($create);

        $this->resource->setResultMode(2);
        $data = $this->resource->getOne($variant->getId(), [
            'considerTaxInput' => true,
        ]);

        static::assertEquals(400, $data['prices'][0]['price']);
    }

    public function testBatchModeShouldBeSuccessful()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet();
        $data['configuratorSet'] = $configuratorSet;

        $article = $this->resourceArticle->create($data);
        static::assertCount(0, $article->getDetails());

        // Create 5 new variants
        $batchData = [];
        for ($i = 0; $i < 5; ++$i) {
            $create = $this->getSimpleVariantData();
            $create['articleId'] = $article->getId();
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
        $this->resourceArticle->setResultMode(Variant::HYDRATE_ARRAY);
        $id = $article->getId();
        $article = $this->resourceArticle->getOne($id);

        static::assertCount(5, $article['details']);
        static::assertEquals(398, round($article['mainDetail']['prices'][0]['price']));
    }

    public function testNewConfiguratorOptionForVariant()
    {
        $data = $this->getSimpleArticleData();
        $data['mainDetail'] = $this->getSimpleVariantData();
        $configuratorSet = $this->getSimpleConfiguratorSet(1, 2);
        $data['configuratorSet'] = $configuratorSet;

        $article = $this->resourceArticle->create($data);

        // Create 5 new variants
        $batchData = [];
        $names = [];
        for ($i = 0; $i < 5; ++$i) {
            $create = $this->getSimpleVariantData();
            $create['articleId'] = $article->getId();

            $options = $this->getVariantOptionsOfSet($configuratorSet);

            unset($options[0]['optionId']);
            $name = 'New-' . uniqid(rand());
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

            static::assertCount(1, $variant['configuratorOptions']);

            $option = $variant['configuratorOptions'][0];

            static::assertContains($option['name'], $names);
        }
    }

    public function testCreateConfiguratorOptionsWithPosition()
    {
        // required field name is missing
        $testData = [
            'name' => 'Testartikel',
            'taxId' => 1,
            'supplierId' => 2,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid(rand()),
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

        $article = $this->resourceArticle->create($testData);
        static::assertInstanceOf('\Shopware\Models\Article\Article', $article);
        static::assertGreaterThan(0, $article->getId());

        $groups = $article->getConfiguratorSet()->getGroups();
        static::assertCount(2, $groups);

        /** @var Group[] $groups */
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

    public function testCreateEsdVariant()
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand1' . uniqid(rand()),
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

        $article = $this->resourceArticle->create($params);

        static::assertInstanceOf(Esd::class, $article->getMainDetail()->getEsd());
        static::assertEquals('shopware_logo.png', $article->getMainDetail()->getEsd()->getFile());
    }

    public function testCreateEsdWithSerialsVariant()
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand2' . uniqid(rand()),
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

        $article = $this->resourceArticle->create($params);

        static::assertInstanceOf(Esd::class, $article->getMainDetail()->getEsd());
        static::assertEquals(5, $article->getMainDetail()->getEsd()->getSerials()->count());
        static::assertTrue($article->getMainDetail()->getEsd()->getHasSerials());
        static::assertEquals('shopware_logo.png', $article->getMainDetail()->getEsd()->getFile());
    }

    /**
     * @depends testCreateEsdVariant
     */
    public function testCreateEsdReuseVariant()
    {
        $params = [
            'name' => 'My awesome liquor',
            'description' => 'hmmmmm',
            'active' => true,
            'taxId' => 1,
            'mainDetail' => [
                'number' => 'brand2' . uniqid(rand()),
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

        $article = $this->resourceArticle->create($params);

        static::assertInstanceOf(Esd::class, $article->getMainDetail()->getEsd());
        static::assertEquals(5, $article->getMainDetail()->getEsd()->getSerials()->count());
        static::assertTrue($article->getMainDetail()->getEsd()->getHasSerials());
        static::assertNotEquals('shopware_logo.png', $article->getMainDetail()->getEsd()->getFile());
    }

    private function getVariantOptionsOfSet($configuratorSet)
    {
        $options = [];
        foreach ($configuratorSet['groups'] as $group) {
            $id = rand(0, count($group['options']) - 1);
            $option = $group['options'][$id];
            $options[] = [
                'optionId' => $option['id'],
                'groupId' => $group['id'],
            ];
        }

        return $options;
    }

    private function getSimpleMedia($limit = 5, $offset = 0)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('media.id  as mediaId')
            ->from('Shopware\Models\Media\Media', 'media')
            ->where('media.albumId = -1')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder->getQuery()->getArrayResult();
    }

    private function getRandomId($table)
    {
        return Shopware()->Db()->fetchOne('SELECT id FROM ' . $table . ' LIMIT 1');
    }

    private function getSimpleVariantData()
    {
        return [
            'number' => 'swTEST' . uniqid(rand()),
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

    private function getSimpleArticleData()
    {
        return [
            'name' => 'Images - Test Artikel',
            'description' => 'Test description',
            'active' => true,
            'taxId' => 1,
            'supplierId' => 2,
        ];
    }

    private function getSimpleConfiguratorSet($groupLimit = 3, $optionLimit = 5)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['groups.id'])
            ->from('Shopware\Models\Article\Configurator\Group', 'groups')
            ->setFirstResult(0)
            ->setMaxResults($groupLimit)
            ->orderBy('groups.position', 'ASC');

        $groups = $builder->getQuery()->getArrayResult();

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['options.id'])
            ->from('Shopware\Models\Article\Configurator\Option', 'options')
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
