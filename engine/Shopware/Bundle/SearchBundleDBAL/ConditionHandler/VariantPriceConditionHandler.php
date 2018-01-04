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

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\CriteriaAwareInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\VariantHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantPriceConditionHandler implements ConditionHandlerInterface, CriteriaAwareInterface
{
    /**
     * @var ConditionHandlerInterface
     */
    private $priceHandler;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var VariantHelper
     */
    private $variantHelper;

    /**
     * @param ConditionHandlerInterface $priceHandler
     * @param VariantHelper             $variantHelper
     */
    public function __construct(ConditionHandlerInterface $priceHandler, VariantHelper $variantHelper)
    {
        $this->priceHandler = $priceHandler;
        $this->variantHelper = $variantHelper;
    }

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param ConditionInterface   $condition
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        if (!$this->criteria->hasConditionOfClass(VariantCondition::class)) {
            $this->priceHandler->generateCondition($condition, $query, $context);

            return;
        }

        $this->variantHelper->joinPrices($query, $context, $this->criteria);

        $suffix = md5(json_encode($condition));

        $minKey = ':priceMin' . $suffix;
        $maxKey = ':priceMax' . $suffix;

        /** @var PriceCondition $condition */
        if ($condition->getMaxPrice() > 0 && $condition->getMinPrice() > 0) {
            $query->andWhere('listing_price.price BETWEEN ' . $minKey . ' AND ' . $maxKey);

            $query->setParameter($minKey, $condition->getMinPrice());
            $query->setParameter($maxKey, $condition->getMaxPrice());

            return;
        }
        if ($condition->getMaxPrice() > 0) {
            $query->andWhere('listing_price.price <= ' . $maxKey);
            $query->setParameter($maxKey, $condition->getMaxPrice());

            return;
        }

        if ($condition->getMinPrice() > 0) {
            $query->andWhere('listing_price.price >= ' . $minKey);
            $query->setParameter($minKey, $condition->getMinPrice());

            return;
        }
    }

    /**
     * @param Criteria $criteria
     */
    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     *
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $this->priceHandler->supportsCondition($condition);
    }
}
