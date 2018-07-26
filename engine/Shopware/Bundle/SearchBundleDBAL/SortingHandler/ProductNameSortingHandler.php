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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductNameSortingHandler implements SortingHandlerInterface
{
    const TRANSLATION_IDENTIFIER = 's:10:"txtArtikel";s:';

    const TRANSLATION = 'productTranslationName';

    const TRANSLATION_OBJECT_DATA = self::TRANSLATION . '.objectdata';

    const TRANSLATION_OBJECT_KEY = self::TRANSLATION . '.objectkey';

    const TRANSLATION_OBJECT_LANGUAGE = self::TRANSLATION . '.objectlanguage';

    const TRANSLATION_OBJECT_TYPE = self::TRANSLATION . '.objecttype';

    const PRODUCT = 'product';

    const PRODUCT_ID = self::PRODUCT . '.id';

    const PRODUCT_NAME = self::PRODUCT . '.name';

    const S_CORE_TRANSLATIONS = 's_core_translations';

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
            self::S_CORE_TRANSLATIONS,
            self::TRANSLATION,
            $query->expr()->andX(
                $query->expr()->eq(self::TRANSLATION_OBJECT_TYPE, $query->expr()->literal('article')),
                $query->expr()->eq(self::TRANSLATION_OBJECT_KEY, self::PRODUCT_ID),
                $query->expr()->eq(self::TRANSLATION_OBJECT_LANGUAGE, $context->getShop()->getId()),
                $query->expr()->like(self::TRANSLATION_OBJECT_DATA, $query->expr()->literal('%' . self::TRANSLATION_IDENTIFIER . '%'))
            )
        );

        $query->addOrderBy(
            self::exprIf(
                $query->expr()->isNull(self::TRANSLATION_OBJECT_DATA),
                self::PRODUCT_NAME,
                self::exprSubstringIndex(
                    self::exprSubstring(
                        self::TRANSLATION_OBJECT_DATA,
                        self::exprAdd(
                            self::exprLocate(
                                $query->expr()->literal(':'),
                                self::TRANSLATION_OBJECT_DATA,
                                self::exprAdd(
                                    self::exprLocate($query->expr()->literal(self::TRANSLATION_IDENTIFIER),
                                    self::TRANSLATION_OBJECT_DATA),
                                    strlen(self::TRANSLATION_IDENTIFIER)
                                )
                            ),
                            '2'
                        )
                    ),
                    $query->expr()->literal('"'),
                    1
                )
            ),
            $sorting->getDirection()
        );
    }

    /**
     * @param string $condition
     * @param string $expression1
     * @param string $expression2
     * @return string
     */
    protected static function exprIf($condition, $expression1, $expression2)
    {
        return " if (($condition),($expression1),($expression2)) ";
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param int    $count
     * @return string
     */
    protected static function exprSubstringIndex($haystack, $needle, $count)
    {
        return " substring_index(($haystack),($needle),($count)) ";
    }

    /**
     * @param string          $target
     * @param int|string      $start
     * @param int|string|null $count
     * @return string
     */
    protected static function exprSubstring($target, $start, $length = null)
    {
        $length = empty($length) ? '' : ",($length)";

        return " substring(($target),($start)$length) ";
    }

    /**
     * @param string          $needle
     * @param string          $haystack
     * @param int|string|null $position
     * @return string
     */
    protected static function exprLocate($needle, $haystack, $position = null)
    {
        $position = empty($position) ? '' : ",($position)";

        return " locate(($needle),($haystack)$position) ";
    }

    /**
     * @param string $expression1
     * @param string $expression2
     * @return string
     */
    protected static function exprAdd($expression1, $expression2)
    {
        return " (($expression1)+($expression2)) ";
    }
}
