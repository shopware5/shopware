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

namespace Shopware\Components\Translation;

use Doctrine\DBAL\Connection;
use Shopware\Models\Article\Detail as ProductVariant;

class SwapProductVariantTranslationService implements SwapProductVariantTranslationServiceInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function swapProductVariantTranslation(ProductVariant $newMainVariant, ProductVariant $oldMainVariant): void
    {
        $productId = $newMainVariant->getArticle()->getId();

        $oldMainVariantTranslations = $this->getTranslations(self::OBJECT_TYPE_PRODUCT, $productId);
        $newMainVariantTranslations = $this->getTranslations(self::OBJECT_TYPE_VARIANT, $newMainVariant->getId());

        $this->updateTranslation(self::OBJECT_TYPE_VARIANT, (int) $oldMainVariant->getId(), $oldMainVariantTranslations);
        $this->updateTranslation(self::OBJECT_TYPE_PRODUCT, (int) $productId, $newMainVariantTranslations);
    }

    /**
     * @param array<int|string, array<string, mixed>> $translationsToUpdate
     */
    private function updateTranslation(string $objectType, int $objectKey, array $translationsToUpdate): void
    {
        $translationIdsToUpdate = array_map(function ($translation) {
            return (int) $translation[self::TRANSLATION_ID_KEY];
        }, $translationsToUpdate);

        $this->connection->createQueryBuilder()
            ->update('s_core_translations')
            ->set('objecttype', ':objectType')
            ->set('objectkey', ':objectKey')
            ->where('id IN (:ids)')
            ->setParameter('objectType', $objectType)
            ->setParameter('objectKey', $objectKey)
            ->setParameter('ids', $translationIdsToUpdate, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function getTranslations(string $objectType, int $objectKey): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select(['objectlanguage as arrayIndex', 'objectlanguage', 'id', 'objectdata', 'objecttype', 'objectkey'])
            ->from('s_core_translations')
            ->where('objecttype = :objecttype')
            ->andWhere('objectkey = :objectkey')
            ->setParameter('objecttype', $objectType)
            ->setParameter('objectkey', $objectKey)
            ->execute()
            ->fetchAllAssociativeIndexed();
    }
}
