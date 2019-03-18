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

use Shopware\Bundle\SearchBundle\Condition\IsAvailableCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\CriteriaAwareInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class IsAvailableConditionHandler implements ConditionHandlerInterface, CriteriaAwareInterface
{
    /**
     * @var PriceHelperInterface
     */
    private $priceHelper;

    /**
     * @var Criteria
     */
    private $criteria;

    public function __construct(PriceHelperInterface $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    public function setCriteria(Criteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof IsAvailableCondition;
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

        if (empty($conditions)) {
            //variants will ne be splitted => only check if product has an available variant
            $this->priceHelper->joinAvailableVariant($query);

            return;
        }

        //variants will be displayed => add stock condition
        $query->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)');
    }
}
