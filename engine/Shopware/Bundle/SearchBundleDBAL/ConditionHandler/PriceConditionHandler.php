<?php
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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\CriteriaAwareInterface;
use Shopware\Bundle\SearchBundleDBAL\ListingPriceSwitcher;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PriceConditionHandler implements ConditionHandlerInterface, CriteriaAwareInterface
{
    public const LISTING_PRICE_JOINED = 'listing_price';

    /**
     * @var ListingPriceSwitcher
     */
    private $priceSwitcher;

    /**
     * @var Criteria
     */
    private $criteria;

    public function __construct(ListingPriceSwitcher $priceSwitcher)
    {
        $this->priceSwitcher = $priceSwitcher;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof PriceCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->addCondition($condition, $query, $context);
    }

    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    private function addCondition(PriceCondition $condition, QueryBuilder $query, ShopContextInterface $context): void
    {
        $this->priceSwitcher->joinPrice($query, $this->criteria, $context);

        $suffix = md5(json_encode($condition, JSON_THROW_ON_ERROR));

        $minKey = ':priceMin' . $suffix;
        $maxKey = ':priceMax' . $suffix;

        if ($condition->getMaxPrice() > 0 && $condition->getMinPrice() > 0) {
            $query->andWhere('listing_price.cheapest_price BETWEEN ' . $minKey . ' AND ' . $maxKey);
            $query->setParameter($minKey, $condition->getMinPrice());
            $query->setParameter($maxKey, $condition->getMaxPrice());

            return;
        }
        if ($condition->getMaxPrice() > 0) {
            $query->andWhere('listing_price.cheapest_price <= ' . $maxKey);
            $query->setParameter($maxKey, $condition->getMaxPrice());

            return;
        }

        if ($condition->getMinPrice() > 0) {
            $query->andWhere('listing_price.cheapest_price >= ' . $minKey);
            $query->setParameter($minKey, $condition->getMinPrice());
        }
    }
}
