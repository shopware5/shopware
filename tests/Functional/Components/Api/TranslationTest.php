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

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Translation as TranslationResource;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Translation\Translation;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class TranslationTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const PRODUCT_TYPE = 'article';

    /**
     * @var TranslationResource
     */
    protected $resource;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function createResource(): TranslationResource
    {
        return new TranslationResource();
    }

    public function testList(): void
    {
        $list = $this->resource->getList(0, 5);
        static::assertCount(5, $list['data']);

        foreach ($list['data'] as $item) {
            static::assertArrayHasKey('shopId', $item);
        }
    }

    public function testProductTranslationList(): void
    {
        $list = $this->resource->getList(0, 5, [
            [
                'property' => 'translation.type',
                'value' => TranslationResource::TYPE_PRODUCT,
            ],
        ]);

        foreach ($list['data'] as $item) {
            $product = $this->getContainer()->get('models')->find(ProductModel::class, $item['key']);
            static::assertInstanceOf(ProductModel::class, $product);
            static::assertSame(TranslationResource::TYPE_PRODUCT, $item['type']);
        }
    }

    public function testSingleProductTranslation(): void
    {
        $list = $this->resource->getList(0, 1, [
            [
                'property' => 'translation.type',
                'value' => TranslationResource::TYPE_PRODUCT,
            ],
            [
                'property' => 'translation.key',
                'value' => $this->connection->fetchOne(
                    'SELECT objectkey FROM s_core_translations WHERE objecttype=:type LIMIT 1',
                    ['type' => self::PRODUCT_TYPE]
                ),
            ],
            [
                'property' => 'translation.shopId',
                'value' => 2,
            ],
        ]);

        static::assertCount(1, $list['data']);
        $data = $list['data'][0];

        static::assertSame(TranslationResource::TYPE_PRODUCT, $data['type']);
        static::assertArrayHasKey('name', $data['data']);
        static::assertArrayHasKey('descriptionLong', $data['data']);
    }

    /**
     * Checks if variants can be translated
     *
     * @throws ParameterMissingException
     */
    public function testCreateVariantTranslationByNumber(): void
    {
        $data = $this->getDummyData('variant');
        // Artikel mit Standardkonfigurator rot / 39
        $product = $this->connection->fetchAssociative("SELECT id, ordernumber, articleID FROM s_articles_details WHERE ordernumber = 'SW10201.11'");
        static::assertIsArray($product);
        $data['key'] = $product['ordernumber'];

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertSame((int) $product['id'], $translation->getKey(), 'Translation key do not match');
        static::assertSame($data['type'], $translation->getType());
        static::assertSame(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                self::PRODUCT_TYPE,
                $translation->getData()
            ),
            'Translation data do not match'
        );
    }

    public function testProductTranslationUpdateOverride(): void
    {
        $productId = $this->createProduct();
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
            ['property' => 'translation.key', 'value' => $productId],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);
        $translation = $translation['data'][0];

        foreach ($translation['data'] as &$fieldTranslation) {
            $fieldTranslation = 'UPDATE - ' . $fieldTranslation;
        }
        unset($fieldTranslation);

        $updateResult = $this->resource->update($productId, $translation);
        static::assertInstanceOf(Translation::class, $updateResult);

        $updatedTranslation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
            ['property' => 'translation.key', 'value' => $productId],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);
        $updatedTranslation = $updatedTranslation['data'][0];

        static::assertSame($translation['key'], $updatedTranslation['key'], 'Translation key do not match');
        static::assertSame($translation['type'], $updatedTranslation['type'], 'Translation type do not match');
        static::assertSame($translation['data'], $updatedTranslation['data'], 'Translation data do not match');
    }

    public function testProductTranslationUpdateMerge(): void
    {
        $productId = $this->createProduct();
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
            ['property' => 'translation.key', 'value' => $productId],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);

        $translation = $translation['data'][0];
        $translation['data'] = [
            'txtArtikel' => 'Update-2',
        ];

        $updateResult = $this->resource->update($productId, $translation);
        static::assertInstanceOf(Translation::class, $updateResult);

        $updatedTranslation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
            ['property' => 'translation.key', 'value' => $productId],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);

        $updatedTranslation = $updatedTranslation['data'][0];

        static::assertSame($translation['key'], $updatedTranslation['key'], 'Translation key do not match');
        static::assertSame($translation['type'], $updatedTranslation['type'], 'Translation type do not match');
        static::assertSame($translation['data']['txtArtikel'], $updatedTranslation['data']['name']);
    }

    public function testRecursiveMerge(): void
    {
        $create = $this->getDummyData(self::PRODUCT_TYPE);

        $create['type'] = 'recursive';
        $create['data'] = [
            'a1' => 'create',
            'b1' => [
                'a2' => 'create',
                'b2' => [
                    'a3' => 'create',
                    'b3' => [
                        'a4' => 'create',
                    ],
                ],
            ],
        ];

        $created = $this->resource->create($create);

        $update = $create;
        $update['data'] = [
            'a1' => 'update',
            'b1' => [
                'a2' => 'update',
                'b2' => [
                    'a3' => 'update',
                ],
            ],
        ];

        $updateResult = $this->resource->update($created->getKey(), $update);
        static::assertInstanceOf(Translation::class, $updateResult);

        $updatedTranslation = $this->resource->getList(0, 1, [
            ['property' => 'translation.key', 'value' => $created->getKey()],
        ]);
        $updatedTranslation = $updatedTranslation['data'][0];

        $updateData = $update['data'];
        $updatedData = $updatedTranslation['data'];

        static::assertSame($updateData['a1'], $updatedData['a1'], 'First level not updated');
        static::assertSame($updateData['b1']['a2'], $updatedData['b1']['a2'], 'Second level not updated');
        static::assertSame($updateData['b1']['b2']['a3'], $updatedData['b1']['b2']['a3'], 'Third level not updated');
        static::assertSame($create['data']['b1']['b2']['b3']['a4'], $updatedData['b1']['b2']['b3']['a4'], 'Fourth level not updated');
    }

    public function testBatch(): void
    {
        $translations = [];
        for ($i = 0; $i < 4; ++$i) {
            $translations[] = $this->getDummyData(self::PRODUCT_TYPE);
        }

        $product = $this->connection->fetchAssociative(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            LIMIT 1'
        );
        static::assertIsArray($product);
        $translations[0]['key'] = $product['ordernumber'];
        $translations[0]['useNumberAsId'] = true;

        foreach ($this->resource->batch($translations) as $result) {
            static::assertTrue($result['success']);
            static::assertSame('update', $result['operation']);
            static::assertNotEmpty($result['data']);
            static::assertSame(2, $result['data']['shopId']);
        }
    }

    public function testBatchUpdate(): void
    {
        $productId1 = $this->createProduct();
        $productId2 = $this->createProduct();

        $this->resource->setResultMode(2);
        $translations = $this->resource->getList(
            0,
            2,
            [
                ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
                ['property' => 'translation.shopId', 'value' => 2],
            ],
            [
                ['property' => 'translation.id', 'direction' => 'DESC'],
            ]
        );
        $translations = $translations['data'];

        foreach ($translations as &$translation) {
            static::assertContains($translation['key'], [$productId1, $productId2]);
            foreach ($translation['data'] as &$fieldTranslation) {
                $fieldTranslation = 'UPDATE - ' . $fieldTranslation;
            }
            unset($fieldTranslation);
        }
        unset($translation);

        foreach ($this->resource->batch($translations) as $operation) {
            static::assertTrue($operation['success']);
            static::assertSame('update', $operation['operation']);
        }

        $updatedTranslations = $this->resource->getList(
            0,
            2,
            [
                ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
                ['property' => 'translation.shopId', 'value' => 2],
            ],
            [
                ['property' => 'translation.id', 'direction' => 'DESC'],
            ]
        );
        $updatedTranslations = $updatedTranslations['data'];

        foreach ($updatedTranslations as $key => $updatedTranslation) {
            static::assertSame(
                $translations[$key]['key'],
                $updatedTranslation['key'],
                'Translation key do not match'
            );
            static::assertSame(
                $translations[$key]['type'],
                $updatedTranslation['type'],
                'Translation type do not match'
            );

            $dataTranslation = $updatedTranslation['data'];
            static::assertSame(
                $translations[$key]['data']['name'],
                $dataTranslation['name']
            );
        }
    }

    public function testUpdateByNumber(): void
    {
        $productId = $this->createProductByNumber();
        $translation = $this->getDummyData(self::PRODUCT_TYPE);
        $product = $this->connection->fetchAssociative(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            WHERE articleID = :productId
            LIMIT 1',
            ['productId' => $productId]
        );
        static::assertIsArray($product);
        $translation['key'] = $product['ordernumber'];

        foreach ($translation['data'] as &$data) {
            $data .= '-UpdateByNumber';
        }
        unset($data);

        $result = $this->resource->updateByNumber($product['ordernumber'], $translation);

        static::assertInstanceOf(Translation::class, $result);
        static::assertSame($result->getKey(), (int) $product['articleID']);
        $data = unserialize($result->getData());

        foreach ($data as $item) {
            $isInString = str_contains($item, '-UpdateByNumber');
            static::assertTrue($isInString);
        }
    }

    public function testDelete(): void
    {
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);

        unset($data['data']);

        $result = $this->resource->delete($data['key'], $data);
        static::assertTrue($result);

        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.key', 'value' => $data['key']],
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
        ]);
        static::assertCount(0, $translation['data']);
    }

    public function testDeleteByNumber(): void
    {
        $data = $this->getDummyData(self::PRODUCT_TYPE);

        $product = $this->connection->fetchAssociative(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            LIMIT 1'
        );
        static::assertIsArray($product);
        $data['key'] = $product['articleID'];

        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);

        unset($data['data']);

        $result = $this->resource->deleteByNumber($product['ordernumber'], $data);
        static::assertTrue($result);

        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.key', 'value' => $data['key']],
            ['property' => 'translation.type', 'value' => self::PRODUCT_TYPE],
        ]);
        static::assertCount(0, $translation['data']);
    }

    public function testLinkNumber(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('link');
        $this->resource->createByNumber($data);
    }

    public function testDownloadNumber(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('download');
        $this->resource->createByNumber($data);
    }

    public function testManufacturerNumber(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_articles_supplier LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('supplier', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('supplier', (int) $entity['id'], $entity['name']);
        $this->numberDelete('supplier', $entity['name']);
    }

    public function testCountryName(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_countries LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_countries', (int) $entity['id'], $entity['countryname']);
        $this->numberUpdate('config_countries', (int) $entity['id'], $entity['countryname']);
        $this->numberDelete('config_countries', $entity['countryname']);
    }

    public function testCountryIso(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_countries LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_countries', (int) $entity['id'], $entity['countryiso']);
        $this->numberUpdate('config_countries', (int) $entity['id'], $entity['countryiso']);
        $this->numberDelete('config_countries', $entity['countryiso']);
    }

    public function testCountryStateName(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_countries_states LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_country_states', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_country_states', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_country_states', $entity['name']);
    }

    public function testCountryStateCode(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_countries_states LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_country_states', (int) $entity['id'], $entity['shortcode']);
        $this->numberUpdate('config_country_states', (int) $entity['id'], $entity['shortcode']);
        $this->numberDelete('config_country_states', $entity['shortcode']);
    }

    public function testDispatchName(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_premium_dispatch LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_dispatch', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_dispatch', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_dispatch', $entity['name']);
    }

    public function testPaymentName(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_paymentmeans LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_payment', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_payment', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_payment', $entity['name']);
    }

    public function testPaymentDescription(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_core_paymentmeans LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('config_payment', (int) $entity['id'], $entity['description']);
        $this->numberUpdate('config_payment', (int) $entity['id'], $entity['description']);
        $this->numberDelete('config_payment', $entity['description']);
    }

    public function testFilterSetNumber(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_filter LIMIT 1');
        static::assertIsArray($entity);
        $this->numberCreate('propertygroup', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertygroup', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertygroup', $entity['name']);
    }

    public function testFilterGroupNumber(): void
    {
        $entity = $this->getFilterGroupName();
        static::assertIsArray($entity);
        $this->numberCreate('propertyoption', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertyoption', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertyoption', $entity['name']);
    }

    public function testFilterOptionNumber(): void
    {
        $entity = $this->getFilterOptionName();
        static::assertIsArray($entity);
        $this->numberCreate('propertyvalue', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertyvalue', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertyvalue', $entity['name']);
    }

    public function testConfiguratorGroupNumber(): void
    {
        $entity = $this->connection->fetchAssociative('SELECT * FROM s_article_configurator_groups');
        static::assertIsArray($entity);
        $this->numberCreate('configuratorgroup', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('configuratorgroup', (int) $entity['id'], $entity['name']);
        $this->numberDelete('configuratorgroup', $entity['name']);
    }

    public function testConfiguratorOptionNumber(): void
    {
        $entity = $this->getConfiguratorOptionName();
        static::assertIsArray($entity);
        $this->numberCreate('configuratoroption', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('configuratoroption', (int) $entity['id'], $entity['name']);
        $this->numberDelete('configuratoroption', $entity['name']);
    }

    public function testCreateMissingKey(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        unset($data['key']);
        $this->resource->create($data);
    }

    public function testCreateByNumberMissingKey(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        unset($data['key']);
        $this->resource->createByNumber($data);
    }

    public function testUpdateMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $this->resource->update(0, $data);
    }

    public function testUpdateByNumberMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $this->resource->updateByNumber('', $data);
    }

    public function testDeleteMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $this->resource->delete(0, $data);
    }

    public function testDeleteByNumberMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $this->resource->deleteByNumber('', $data);
    }

    public function testDeleteInvalidTranslation(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $this->resource->delete(-200, $data);
    }

    public function testDeleteByNumberInvalidTranslation(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);

        $product = $this->connection->fetchAssociative('SELECT ordernumber, articleID FROM s_articles_details LIMIT 1');
        static::assertIsArray($product);
        $data['key'] = $product['articleID'];

        $this->resource->create($data);

        $this->resource->delete($data['key'], $data);

        $this->resource->deleteByNumber($product['ordernumber'], $data);
    }

    public function testInvalidTypeByNumber(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $data['type'] = 'Invalid';
        $this->resource->createByNumber($data);
    }

    public function testInvalidProductNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidManufacturerNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('supplier');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidCountryNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('config_countries');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidCountryStateNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('config_country_states');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidDispatchNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('config_dispatch');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidPaymentNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('config_payment');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterSetNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertygroup');
        $data['key'] = 'Invalid-Order-Number';
        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterGroupSyntax(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('propertyoption');

        $name = $this->getFilterGroupName();
        $name = str_replace('|', '>', $name);
        $data['key'] = $name['name'];
        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterGroupSetName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertyoption');
        $name = $this->getFilterGroupName();
        $tmp = explode('|', $name['name']);
        $tmp[0] .= '-INVALID';
        $name = implode('|', $tmp);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterGroupName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertyoption');
        $name = $this->getFilterGroupName();
        $tmp = explode('|', $name['name']);
        $tmp[1] .= '-INVALID';
        $name = implode('|', $tmp);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterOptionSyntax(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('propertyvalue');

        $name = $this->getFilterOptionName();
        $name = str_replace('|', '>', $name);
        $data['key'] = $name['name'];
        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterOptionSetName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertyvalue');
        $name = $this->getFilterOptionName();
        $tmp = explode('|', $name['name']);
        $tmp[0] .= '-INVALID';
        $name = implode('|', $tmp);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterOptionGroupName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertyvalue');
        $name = $this->getFilterOptionName();
        $tmp = explode('|', $name['name']);
        $tmp[1] .= '-INVALID';
        $name = implode('|', $tmp);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidFilterOptionName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('propertyvalue');
        $name = $this->getFilterOptionName();
        $tmp = explode('|', $name['name']);
        $tmp[2] .= '-INVALID';
        $name = implode('|', $tmp);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidConfiguratorGroupName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('configuratorgroup');
        $data['key'] = 'INVALID_NAME';
        $this->resource->createByNumber($data);
    }

    public function testInvalidConfiguratorOptionSyntax(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('configuratoroption');
        $entity = $this->getConfiguratorOptionName();

        $data['key'] = str_replace('|', '>', $entity['name']);

        $this->resource->createByNumber($data);
    }

    public function testInvalidConfiguratorOptionWithGroupName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('configuratoroption');
        $entity = $this->getConfiguratorOptionName();

        $name = explode('|', $entity['name']);
        $name[0] .= '-INVALID';
        $name = implode('|', $name);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testInvalidConfiguratorOptionWithOptionName(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('configuratoroption');
        $entity = $this->getConfiguratorOptionName();

        $name = explode('|', $entity['name']);
        $name[1] .= '-INVALID';
        $name = implode('|', $name);
        $data['key'] = $name;

        $this->resource->createByNumber($data);
    }

    public function testMissingTypeException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        unset($data['type']);
        $this->resource->create($data);
    }

    public function testMissingShopIdException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        unset($data['shopId']);
        $this->resource->create($data);
    }

    public function testMissingDataException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        unset($data['data']);
        $this->resource->create($data);
    }

    public function testMissingDataIsArrayException(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $data['data'] = 1;
        $this->resource->create($data);
    }

    /**
     * Override test method of parent class because the translation resource has no "getOne" method
     *
     * @group disable
     */
    public function testGetOneWithMissingPrivilegeShouldThrowPrivilegeException(): void
    {
        // Do not remove
        static::assertTrue(true);
    }

    /**
     * Override test method of parent class because the translation resource has no "getOne" method
     *
     * @group disable
     */
    public function testGetOneWithInvalidIdShouldThrowNotFoundException(): void
    {
        // Do not remove
        static::assertTrue(true);
    }

    /**
     * Override test method of parent class because the translation resource has no "getOne" method
     *
     * @group disable
     */
    public function testGetOneWithMissingIdShouldThrowParameterMissingException(): void
    {
        // Do not remove
        static::assertTrue(true);
    }

    private function createProduct(): int
    {
        $data = $this->getDummyData(self::PRODUCT_TYPE);

        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);
        static::assertSame(
            $data['key'],
            $translation->getKey(),
            'Translation key do not match'
        );
        static::assertSame(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        static::assertSame(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                self::PRODUCT_TYPE,
                $translation->getData()
            ),
            'Translation data do not match'
        );

        return $translation->getKey();
    }

    private function createProductByNumber(): int
    {
        $data = $this->getDummyData(self::PRODUCT_TYPE);
        $product = $this->connection->fetchAssociative('SELECT ordernumber, articleID FROM s_articles_details LIMIT 1');
        static::assertIsArray($product);
        $data['key'] = $product['ordernumber'];

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);
        static::assertSame((int) $product['articleID'], $translation->getKey(), 'Translation key do not match');
        static::assertSame($data['type'], $translation->getType(), 'Translation type do not match');
        static::assertSame(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                self::PRODUCT_TYPE,
                $translation->getData()
            ),
            'Translation data do not match'
        );

        return (int) $product['articleID'];
    }

    private function numberCreate(string $type, int $id, string $number): void
    {
        $data = $this->getDummyData($type);
        $data['key'] = $number;

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertSame($id, $translation->getKey());

        $translated = $this->resource->getTranslationComponent()->unFilterData(
            $type,
            $translation->getData()
        );

        foreach ($data['data'] as $key => $value) {
            static::assertSame($value, $translated[$key]);
        }
    }

    private function numberUpdate(string $type, int $id, string $number): void
    {
        $data = $this->getDummyData($type);
        foreach ($data['data'] as &$item) {
            $item .= '-UPDATED';
        }
        unset($item);

        $translation = $this->resource->updateByNumber($number, $data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertSame($id, $translation->getKey());

        $translated = $this->resource->getTranslationComponent()->unFilterData(
            $type,
            $translation->getData()
        );

        foreach ($data['data'] as $key => $value) {
            static::assertSame($value, $translated[$key]);
        }
    }

    private function numberDelete(string $type, string $number): void
    {
        $data = $this->getDummyData($type);
        $result = $this->resource->deleteByNumber($number, $data);
        static::assertTrue($result);
    }

    /**
     * @return array{type: string, key: int, data: array<string, string>, shopId: int}
     */
    private function getDummyData(string $type, int $shopId = 2): array
    {
        return [
            'type' => $type,
            'key' => rand(2000, 10000),
            'data' => $this->getTypeFields($type),
            'shopId' => $shopId,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getTypeFields(string $type): array
    {
        switch (strtolower($type)) {
            case self::PRODUCT_TYPE:
                return [
                    'name' => 'Dummy Translation',
                    'description' => 'Dummy Translation',
                    'descriptionLong' => 'Dummy Translation',
                    'shippingTime' => 'Dummy Translation',
                    'additionalText' => 'Dummy Translation',
                    'keywords' => 'Dummy Translation',
                    'packUnit' => 'Dummy Translation',
                ];
            case 'variant':
                return [
                    'shippingTime' => 'Dummy Translation',
                    'additionalText' => 'Dummy Translation',
                    'packUnit' => 'Dummy Translation',
                ];
            case 'link':
            case 'download':
                return [
                    'description' => 'Dummy Translation',
                ];
            case 'config_countries':
                return [
                    'name' => 'Dummy Translation',
                    'description' => 'Dummy Translation',
                ];
            case 'config_units':
                return [
                    'name' => 'Dummy Translation',
                ];
            case 'config_dispatch':
                return [
                    'name' => 'Dummy Translation',
                    'description' => 'Dummy Translation',
                    'statusLink' => 'Dummy Translation',
                ];
            default:
                return [
                    'name' => 'Dummy Translation',
                    'description' => 'Dummy Translation',
                    'link' => 'Dummy Translation',
                ];
        }
    }

    /**
     * @return array<string, string>
     */
    private function getFilterGroupName(): array
    {
        $filterOptions = $this->connection->fetchAssociative(
            "SELECT fo.id, CONCAT(f.name, '|', fo.name) as name
             FROM s_filter_options as fo
                INNER JOIN s_filter_relations as fr
                    ON fr.optionID = fo.id
                INNER JOIN s_filter as f
                    ON f.id = fr.groupID
             LIMIT 1"
        );
        static::assertIsArray($filterOptions);

        return $filterOptions;
    }

    /**
     * @return array<string, string>
     */
    private function getFilterOptionName(): array
    {
        $filterValues = $this->connection->fetchAssociative(
            "SELECT fv.id, CONCAT(f.name, '|', fo.name, '|', fv.value) as name
             FROM s_filter_values as fv
                 INNER JOIN s_filter_options as fo
                     ON fo.id = fv.optionID
                 INNER JOIN s_filter_relations as fr
                     ON fr.optionID = fo.id
                 INNER JOIN s_filter as f
                     ON f.id = fr.groupID
             LIMIT 1"
        );
        static::assertIsArray($filterValues);

        return $filterValues;
    }

    /**
     * @return array<string, string>
     */
    private function getConfiguratorOptionName(): array
    {
        $configuratorGroups = $this->connection->fetchAssociative(
            "SELECT co.id, CONCAT(cg.name, '|', co.name) as name
             FROM s_article_configurator_groups as cg
                 INNER JOIN s_article_configurator_options as co
                     ON co.group_id = cg.id
             LIMIT 1"
        );
        static::assertIsArray($configuratorGroups);

        return $configuratorGroups;
    }
}
