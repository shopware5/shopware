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

namespace Shopware\Tests\Functional\Components\Translation;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Translation\SwapProductVariantTranslationService;
use Shopware\Components\Translation\SwapProductVariantTranslationServiceInterface;
use Shopware\Models\Article\Article as Product;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Translation;

class SwapProductVariantTranslationServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private const PRODUCT_ID = 32100273;
    private const PRODUCT_MAIN_VARIANT_ID = 32100828;
    private const PRODUCT_VARIANT_1_ID = 32100829;
    private const PRODUCT_VARIANT_2_ID = 32100830;
    private const LANGUAGE_ID = 2;

    public function testSwapProductVariantTranslation(): void
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/variant_product_with_attribute_translations.sql');
        static::assertIsString($sql);

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->executeStatement(
            $sql,
            [
                'productId' => self::PRODUCT_ID,
                'mainVariantId' => self::PRODUCT_MAIN_VARIANT_ID,
                'variantIdOne' => self::PRODUCT_VARIANT_1_ID,
                'variantIdTwo' => self::PRODUCT_VARIANT_2_ID,
            ]
        );

        /** @var Product $product */
        $product = $this->getContainer()->get('models')->find(Product::class, self::PRODUCT_ID);

        $swapTranslationService = $this->getSwapTranslationService();

        $oldMainDetails = $product->getDetails()->get(0);
        $newMainDetails = $product->getDetails()->get(1);

        // swap translations for the first time
        $swapTranslationService->swapProductVariantTranslation($newMainDetails, $oldMainDetails);
        // make translation changes
        $translationService = $this->getContainer()->get(Shopware_Components_Translation::class);
        $translationService->write(
            self::LANGUAGE_ID,
            SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_VARIANT,
            self::PRODUCT_MAIN_VARIANT_ID,
            ['__attribute_attr1' => 'Change one', '__attribute_attr2' => 'Change one', '__attribute_attr3' => '']
        );
        // swap translation back
        $swapTranslationService->swapProductVariantTranslation($oldMainDetails, $newMainDetails);
        // make translation changes again
        $translationService = $this->getContainer()->get(Shopware_Components_Translation::class);
        $translationService->write(
            self::LANGUAGE_ID,
            SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_PRODUCT,
            self::PRODUCT_ID,
            ['__attribute_attr1' => 'Change two', '__attribute_attr2' => 'Change two', '__attribute_attr3' => '']
        );

        $allProductTranslationsResult = $this->getAllProductTranslationsResult();

        static::assertCount(6, $allProductTranslationsResult);
        static::assertCount(
            2,
            array_filter($allProductTranslationsResult, function ($translation) {
                return $translation['objecttype'] === SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_PRODUCT;
            })
        );
        static::assertCount(
            4,
            array_filter($allProductTranslationsResult, function ($translation) {
                return $translation['objecttype'] === SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_VARIANT;
            })
        );

        $mainVariantTranslationResult = $this->getTranslations(SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_VARIANT, self::PRODUCT_MAIN_VARIANT_ID, self::LANGUAGE_ID);
        static::assertCount(0, $mainVariantTranslationResult);

        $productTranslationResult = $this->getTranslations(SwapProductVariantTranslationServiceInterface::OBJECT_TYPE_PRODUCT, self::PRODUCT_ID, self::LANGUAGE_ID);
        static::assertCount(1, $productTranslationResult);

        $productTranslationResult = array_shift($productTranslationResult);
        static::assertIsArray($productTranslationResult);
        static::assertArrayHasKey('objectdata', $productTranslationResult);

        $objectDataResult = $productTranslationResult['objectdata'];
        $unserializedObjectData = unserialize($objectDataResult);
        static::assertIsArray($unserializedObjectData);
        static::assertCount(2, $unserializedObjectData);

        foreach ($unserializedObjectData as $result) {
            static::assertSame('Change two', $result);
        }
    }

    private function getSwapTranslationService(): SwapProductVariantTranslationService
    {
        return new SwapProductVariantTranslationService(
            $this->getContainer()->get(Connection::class)
        );
    }

    /**
     * @return array<int,mixed>
     */
    private function getAllProductTranslationsResult(): array
    {
        $queryBuilder = $this->getContainer()->get(Connection::class)->createQueryBuilder();

        return $queryBuilder->select(['*'])
            ->from('s_core_translations')
            ->where('objecttype IN (:objectType)')
            ->andWhere('objectkey IN (:objectKey)')
            ->setParameter('objectType', ['article', 'variant'], Connection::PARAM_STR_ARRAY)
            ->setParameter('objectKey', [self::PRODUCT_ID, self::PRODUCT_MAIN_VARIANT_ID, self::PRODUCT_VARIANT_1_ID, self::PRODUCT_VARIANT_2_ID], Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getTranslations(string $objectType, int $objectKey, int $languageId): array
    {
        $queryBuilder = $this->getContainer()->get(Connection::class)->createQueryBuilder();

        return $queryBuilder->select(['*'])
            ->from('s_core_translations')
            ->where('objecttype = :objecttype')
            ->andWhere('objectkey = :objectkey')
            ->andWhere('objectlanguage = :objectLanguage')
            ->setParameter('objecttype', $objectType)
            ->setParameter('objectkey', $objectKey)
            ->setParameter('objectLanguage', $languageId)
            ->execute()
            ->fetchAllAssociative();
    }
}
