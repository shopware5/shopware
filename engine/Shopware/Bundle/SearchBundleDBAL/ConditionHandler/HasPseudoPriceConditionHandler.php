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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\HasPseudoPriceCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\CriteriaAwareInterface;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceSwitcher;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class HasPseudoPriceConditionHandler implements ConditionHandlerInterface, CriteriaAwareInterface
{
    private const STATE_INCLUDES_PSEUDO_PRICE_VARIANTS = 'PseudoPriceVariants';

    private ListingPriceSwitcher $listingPriceSwitcher;

    private Criteria $criteria;

    private VariantHelperInterface $variantHelper;

    public function __construct(ListingPriceSwitcher $listingPriceSwitcher, VariantHelperInterface $variantHelper)
    {
        $this->listingPriceSwitcher = $listingPriceSwitcher;
        $this->variantHelper = $variantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof HasPseudoPriceCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $conditions = $this->criteria->getConditionsByClass(VariantCondition::class);
        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        if (!$query->hasState(self::STATE_INCLUDES_PSEUDO_PRICE_VARIANTS)) {
            if (empty($conditions)) {
                $this->variantHelper->joinVariants($query);
                $query->leftJoin('allVariants', 's_articles_prices', 'variantPrices', 'variantPrices.articledetailsID = allVariants.id');
                $query->andWhere('variantPrices.pseudoprice > 0');
            } else {
                $this->listingPriceSwitcher->joinPrice($query, $this->criteria, $context);
                $query->andWhere('listing_price.pseudoprice > 0');
            }
            $query->addState(self::STATE_INCLUDES_PSEUDO_PRICE_VARIANTS);
        }
    }

    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }
}
