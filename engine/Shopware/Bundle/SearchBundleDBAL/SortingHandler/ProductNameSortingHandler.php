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

namespace Shopware\Bundle\SearchBundleDBAL\SortingHandler;

use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductNameSortingHandler implements SortingHandlerInterface
{
    const TRANSLATION = 'productTranslationName';

    const TRANSLATION_NAME = self::TRANSLATION . '.name';

    const PRODUCT = 'product';

    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting instanceof ProductNameSorting;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /* @var ProductNameSorting $sorting */
        $query->leftJoin(
            self::PRODUCT,
            's_articles_translations',
            self::TRANSLATION,
            $query->expr()->andX(
                $query->expr()->eq(self::TRANSLATION . '.articleID', self::PRODUCT . '.id'),
                $query->expr()->eq(self::TRANSLATION . '.languageID', $context->getShop()->getId()),
                $query->expr()->isNotNull(self::TRANSLATION_NAME),
                $query->expr()->neq(self::TRANSLATION_NAME, $query->expr()->literal(''))
            )
        );

        $query->addOrderBy(
            self::exprIf(
                $query->expr()->isNull(self::TRANSLATION_NAME),
                self::PRODUCT . '.name',
                self::TRANSLATION_NAME
            ),
            $sorting->getDirection()
        );
    }

    /**
     * @param string $condition
     * @param string $expression1
     * @param string $expression2
     *
     * @return string
     */
    protected static function exprIf($condition, $expression1, $expression2)
    {
        return " if (($condition),($expression1),($expression2)) ";
    }
}
