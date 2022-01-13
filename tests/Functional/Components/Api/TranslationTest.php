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

use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Translation as TranslationResource;
use Shopware\Models\Article\Article;
use Shopware\Models\Translation\Translation;

class TranslationTest extends TestCase
{
    /**
     * @var TranslationResource
     */
    protected $resource;

    /**
     * @return TranslationResource
     */
    public function createResource()
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

    public function testArticleTranslationList(): void
    {
        $list = $this->resource->getList(0, 5, [
            [
                'property' => 'translation.type',
                'value' => TranslationResource::TYPE_PRODUCT,
            ],
        ]);

        foreach ($list['data'] as $item) {
            $article = Shopware()->Models()->find(Article::class, $item['key']);

            static::assertInstanceOf(Article::class, $article);

            static::assertEquals(
                TranslationResource::TYPE_PRODUCT,
                $item['type']
            );
        }
    }

    public function testSingleArticleTranslation(): void
    {
        $list = $this->resource->getList(0, 1, [
            [
                'property' => 'translation.type',
                'value' => TranslationResource::TYPE_PRODUCT,
            ],
            [
                'property' => 'translation.key',
                'value' => Shopware()->Db()->fetchOne("SELECT objectkey FROM s_core_translations WHERE objecttype='article' LIMIT 1"),
            ],
            [
                'property' => 'translation.shopId',
                'value' => 2,
            ],
        ]);

        static::assertCount(1, $list['data']);
        $data = $list['data'][0];

        static::assertEquals(
            TranslationResource::TYPE_PRODUCT,
            $data['type']
        );

        static::assertArrayHasKey('name', $data['data']);
        static::assertArrayHasKey('descriptionLong', $data['data']);
    }

    public function testCreateArticle(): int
    {
        $data = $this->getDummyData('article');

        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);
        static::assertEquals(
            $data['key'],
            $translation->getKey(),
            'Translation key do not match'
        );
        static::assertEquals(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        static::assertEquals(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                'article',
                $translation->getData()
            ),
            'Translation data do not match'
        );

        return $translation->getKey();
    }

    public function testCreateArticleByNumber(): int
    {
        $data = $this->getDummyData('article');
        $article = Shopware()->Db()->fetchRow('SELECT ordernumber, articleID FROM s_articles_details LIMIT 1');
        $data['key'] = $article['ordernumber'];

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertEquals(
            $article['articleID'],
            $translation->getKey(),
            'Translation key do not match'
        );

        static::assertEquals(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        static::assertEquals(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                'article',
                $translation->getData()
            ),
            'Translation data do not match'
        );

        return (int) $article['articleID'];
    }

    /**
     * Checks if variants can be translated
     *
     * @throws ParameterMissingException
     */
    public function testCreateVariantTranslationByNumber(): void
    {
        $data = $this->getDummyData('variant');
        //Artikel mit Standardkonfigurator rot / 39
        $article = Shopware()->Db()->fetchRow("SELECT id, ordernumber, articleID FROM s_articles_details WHERE ordernumber = 'SW10201.11'");
        $data['key'] = $article['ordernumber'];

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertEquals(
            $article['id'],
            $translation->getKey(),
            'Translation key do not match'
        );

        static::assertEquals(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        static::assertEquals(
            $data['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                'article',
                $translation->getData()
            ),
            'Translation data do not match'
        );
    }

    /**
     * @depends testCreateArticle
     */
    public function testArticleUpdateOverride(int $key): int
    {
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => 'article'],
            ['property' => 'translation.key', 'value' => $key],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);

        $translation = $translation['data'][0];

        foreach ($translation['data'] as &$fieldTranslation) {
            $fieldTranslation = 'UPDATE - ' . $fieldTranslation;
        }

        $updated = $this->resource->update($key, $translation);

        static::assertEquals(
            $translation['key'],
            $updated->getKey(),
            'Translation key do not match'
        );
        static::assertEquals(
            $translation['type'],
            $updated->getType(),
            'Translation type do not match'
        );

        static::assertEquals(
            $translation['data'],
            $this->resource->getTranslationComponent()->unFilterData(
                'article',
                $updated->getData()
            ),
            'Translation data do not match'
        );

        return $key;
    }

    /**
     * @depends testArticleUpdateOverride
     */
    public function testArticleUpdateMerge(int $key): void
    {
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, [
            ['property' => 'translation.type', 'value' => 'article'],
            ['property' => 'translation.key', 'value' => $key],
            ['property' => 'translation.shopId', 'value' => 2],
        ]);

        $translation = $translation['data'][0];
        $translation['data'] = [
            'txtArtikel' => 'Update-2',
        ];

        $updated = $this->resource->update($key, $translation);

        static::assertEquals(
            $translation['key'],
            $updated->getKey(),
            'Translation key do not match'
        );
        static::assertEquals(
            $translation['type'],
            $updated->getType(),
            'Translation type do not match'
        );

        $dataTranslation = unserialize($updated->getData());
        static::assertEquals(
            $translation['data']['txtArtikel'],
            $dataTranslation['txtArtikel']
        );

        static::assertEquals(
            'UPDATE - Dummy Translation',
            $dataTranslation['txtlangbeschreibung']
        );
    }

    public function testRecursiveMerge(): void
    {
        $create = $this->getDummyData('article');

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

        $updated = $this->resource->update($created->getKey(), $update);

        $updateData = $update['data'];
        $updatedData = unserialize($updated->getData());

        static::assertEquals(
            $updateData['a1'],
            $updatedData['a1'],
            'First level not updated'
        );

        static::assertEquals(
            $updateData['b1']['a2'],
            $updatedData['b1']['a2'],
            'Second level not updated'
        );

        static::assertEquals(
            $updateData['b1']['b2']['a3'],
            $updatedData['b1']['b2']['a3'],
            'Third level not updated'
        );

        static::assertEquals(
            $create['data']['b1']['b2']['b3']['a4'],
            $updatedData['b1']['b2']['b3']['a4'],
            'Fourth level not updated'
        );
    }

    public function testBatch(): void
    {
        $translations = [];
        for ($i = 0; $i < 4; ++$i) {
            $translations[] = $this->getDummyData('article');
        }

        $article = Shopware()->Db()->fetchRow(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            LIMIT 1'
        );
        $translations[0]['key'] = $article['ordernumber'];
        $translations[0]['useNumberAsId'] = true;

        $results = $this->resource->batch($translations);

        foreach ($results as $result) {
            static::assertTrue($result['success']);
            static::assertEquals('update', $result['operation']);
            static::assertNotEmpty($result['data']);
            static::assertEquals(2, $result['data']['shopId']);
        }
    }

    /**
     * @depends testCreateArticleByNumber
     */
    public function testUpdateByNumber(int $productId): void
    {
        $translation = $this->getDummyData('article');
        $product = Shopware()->Db()->fetchRow(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            WHERE articleID = :articleId
            LIMIT 1',
            [':articleId' => $productId]
        );
        $translation['key'] = $product['ordernumber'];

        foreach ($translation['data'] as &$data) {
            $data .= '-UpdateByNumber';
        }

        $result = $this->resource->updateByNumber($product['ordernumber'], $translation);

        static::assertInstanceOf(Translation::class, $result);
        static::assertEquals($result->getKey(), $product['articleID']);
        $data = unserialize($result->getData());

        foreach ($data as $item) {
            $isInString = str_contains($item, '-UpdateByNumber');
            static::assertTrue($isInString);
        }
    }

    public function testDelete(): void
    {
        $data = $this->getDummyData('article');
        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);

        unset($data['data']);

        $result = $this->resource->delete($data['key'], $data);

        static::assertTrue($result);
    }

    public function testDeleteByNumber(): void
    {
        $data = $this->getDummyData('article');

        $article = Shopware()->Db()->fetchRow(
            'SELECT ordernumber, articleID
            FROM s_articles_details
            LIMIT 1'
        );
        $data['key'] = $article['articleID'];

        $translation = $this->resource->create($data);

        static::assertInstanceOf(Translation::class, $translation);

        unset($data['data']);

        $result = $this->resource->deleteByNumber($article['ordernumber'], $data);

        static::assertTrue($result);
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
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_articles_supplier LIMIT 1');
        $this->numberCreate('supplier', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('supplier', (int) $entity['id'], $entity['name']);
        $this->numberDelete('supplier', $entity['name']);
    }

    public function testCountryName(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_countries LIMIT 1');
        $this->numberCreate('config_countries', (int) $entity['id'], $entity['countryname']);
        $this->numberUpdate('config_countries', (int) $entity['id'], $entity['countryname']);
        $this->numberDelete('config_countries', $entity['countryname']);
    }

    public function testCountryIso(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_countries LIMIT 1');
        $this->numberCreate('config_countries', (int) $entity['id'], $entity['countryiso']);
        $this->numberUpdate('config_countries', (int) $entity['id'], $entity['countryiso']);
        $this->numberDelete('config_countries', $entity['countryiso']);
    }

    public function testCountryStateName(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_countries_states LIMIT 1');
        $this->numberCreate('config_country_states', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_country_states', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_country_states', $entity['name']);
    }

    public function testCountryStateCode(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_countries_states LIMIT 1');
        $this->numberCreate('config_country_states', (int) $entity['id'], $entity['shortcode']);
        $this->numberUpdate('config_country_states', (int) $entity['id'], $entity['shortcode']);
        $this->numberDelete('config_country_states', $entity['shortcode']);
    }

    public function testDispatchName(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_premium_dispatch LIMIT 1');
        $this->numberCreate('config_dispatch', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_dispatch', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_dispatch', $entity['name']);
    }

    public function testPaymentName(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_paymentmeans LIMIT 1');
        $this->numberCreate('config_payment', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('config_payment', (int) $entity['id'], $entity['name']);
        $this->numberDelete('config_payment', $entity['name']);
    }

    public function testPaymentDescription(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_core_paymentmeans LIMIT 1');
        $this->numberCreate('config_payment', (int) $entity['id'], $entity['description']);
        $this->numberUpdate('config_payment', (int) $entity['id'], $entity['description']);
        $this->numberDelete('config_payment', $entity['description']);
    }

    public function testFilterSetNumber(): void
    {
        $entity = Shopware()->Db()->fetchRow('SELECT * FROM s_filter LIMIT 1');

        $this->numberCreate('propertygroup', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertygroup', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertygroup', $entity['name']);
    }

    public function testFilterGroupNumber(): void
    {
        $entity = $this->getFilterGroupName();

        $this->numberCreate('propertyoption', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertyoption', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertyoption', $entity['name']);
    }

    public function testFilterOptionNumber(): void
    {
        $entity = $this->getFilterOptionName();

        $this->numberCreate('propertyvalue', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('propertyvalue', (int) $entity['id'], $entity['name']);
        $this->numberDelete('propertyvalue', $entity['name']);
    }

    public function testConfiguratorGroupNumber(): void
    {
        $entity = Shopware()->Db()->fetchRow('
            SELECT * FROM s_article_configurator_groups
        ');

        $this->numberCreate('configuratorgroup', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('configuratorgroup', (int) $entity['id'], $entity['name']);
        $this->numberDelete('configuratorgroup', $entity['name']);
    }

    public function testConfiguratorOptionNumber(): void
    {
        $entity = $this->getConfiguratorOptionName();

        $this->numberCreate('configuratoroption', (int) $entity['id'], $entity['name']);
        $this->numberUpdate('configuratoroption', (int) $entity['id'], $entity['name']);
        $this->numberDelete('configuratoroption', $entity['name']);
    }

    private function numberCreate(string $type, int $id, string $number): void
    {
        $data = $this->getDummyData($type);
        $data['key'] = $number;

        $translation = $this->resource->createByNumber($data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertEquals($id, $translation->getKey());

        $translated = $this->resource->getTranslationComponent()->unFilterData(
            $type,
            $translation->getData()
        );

        foreach ($data['data'] as $key => $value) {
            static::assertEquals($value, $translated[$key]);
        }
    }

    private function numberUpdate(string $type, int $id, string $number): void
    {
        $data = $this->getDummyData($type);
        foreach ($data['data'] as &$item) {
            $item .= '-UPDATED';
        }

        $translation = $this->resource->updateByNumber($number, $data);

        static::assertInstanceOf(Translation::class, $translation);

        static::assertEquals($id, $translation->getKey());

        $translated = $this->resource->getTranslationComponent()->unFilterData(
            $type,
            $translation->getData()
        );

        foreach ($data['data'] as $key => $value) {
            static::assertEquals($value, $translated[$key]);
        }
    }

    private function numberDelete(string $type, string $number): void
    {
        $data = $this->getDummyData($type);
        $result = $this->resource->deleteByNumber($number, $data);
        static::assertTrue($result);
    }

    public function testCreateMissingKey(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        unset($data['key']);
        $this->resource->create($data);
    }

    public function testCreateByNumberMissingKey(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        unset($data['key']);
        $this->resource->createByNumber($data);
    }

    public function testUpdateMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        $this->resource->update(0, $data);
    }

    public function testUpdateByNumberMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        $this->resource->updateByNumber('', $data);
    }

    public function testDeleteMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        $this->resource->delete(0, $data);
    }

    public function testDeleteByNumberMissingId(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        $this->resource->deleteByNumber('', $data);
    }

    public function testDeleteInvalidTranslation(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('article');
        $this->resource->delete(-200, $data);
    }

    public function testDeleteByNumberInvalidTranslation(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('article');

        $article = Shopware()->Db()->fetchRow('SELECT ordernumber, articleID FROM s_articles_details LIMIT 1');
        $data['key'] = $article['articleID'];

        $this->resource->create($data);

        $this->resource->delete($data['key'], $data);

        $this->resource->deleteByNumber($article['ordernumber'], $data);
    }

    public function testInvalidTypeByNumber(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('article');
        $data['type'] = 'Invalid';
        $this->resource->createByNumber($data);
    }

    public function testInvalidArticleNumber(): void
    {
        $this->expectException(NotFoundException::class);
        $data = $this->getDummyData('article');
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
        $data = $this->getDummyData('article');
        unset($data['type']);
        $this->resource->create($data);
    }

    public function testMissingshopIdException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        unset($data['shopId']);
        $this->resource->create($data);
    }

    public function testMissingDataException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $data = $this->getDummyData('article');
        unset($data['data']);
        $this->resource->create($data);
    }

    public function testMissingDataIsArrayException(): void
    {
        $this->expectException(CustomValidationException::class);
        $data = $this->getDummyData('article');
        $data['data'] = 1;
        $this->resource->create($data);
    }

    /**
     * @group disable
     */
    public function testGetOneWithMissingPrivilegeShouldThrowPrivilegeException(): void
    {
        static::assertTrue(true);
    }

    /**
     * @group disable
     */
    public function testGetOneWithInvalidIdShouldThrowNotFoundException(): void
    {
        static::assertTrue(true);
    }

    /**
     * @group disable
     */
    public function testGetOneWithMissingIdShouldThrowParameterMissingException(): void
    {
        static::assertTrue(true);
    }

    /**
     * @return array{type: string, key: int, data: array, shopId: int}
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
            case 'article':
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
        return Shopware()->Db()->fetchRow(
            "SELECT fo.id, CONCAT(f.name, '|', fo.name) as name
             FROM s_filter_options as fo
                INNER JOIN s_filter_relations as fr
                    ON fr.optionID = fo.id
                INNER JOIN s_filter as f
                    ON f.id = fr.groupID
             LIMIT 1"
        );
    }

    /**
     * @return array<string, string>
     */
    private function getFilterOptionName(): array
    {
        return Shopware()->Db()->fetchRow(
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
    }

    /**
     * @return array<string, string>
     */
    private function getConfiguratorOptionName(): array
    {
        return Shopware()->Db()->fetchRow(
            "SELECT co.id, CONCAT(cg.name, '|', co.name) as name
             FROM s_article_configurator_groups as cg
                 INNER JOIN s_article_configurator_options as co
                     ON co.group_id = cg.id
             LIMIT 1"
        );
    }
}
