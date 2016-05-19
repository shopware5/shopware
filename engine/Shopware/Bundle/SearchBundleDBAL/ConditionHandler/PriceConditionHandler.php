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
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\ConditionHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @param PriceHelper $priceHelper
     */
    public function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof PriceCondition);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $selection = $this->priceHelper->getSelection($context);
        $selection = 'MIN('.$selection.')';

        $this->priceHelper->joinPrices($query, $context);

        /** @var PriceCondition $condition */
        if ($condition->getMaxPrice() > 0 && $condition->getMinPrice() > 0) {
            $query->andHaving($selection . ' BETWEEN :priceMin AND :priceMax');
            $query->setParameter(':priceMin', $condition->getMinPrice());
            $query->setParameter(':priceMax', $condition->getMaxPrice());
        } elseif ($condition->getMaxPrice() > 0) {
            $query->andHaving($selection . ' <= :priceMax');
            $query->setParameter(':priceMax', $condition->getMaxPrice());
        } elseif ($condition->getMinPrice() > 0) {
            $query->andHaving($selection . ' >= :priceMin');
            $query->setParameter(':priceMin', $condition->getMinPrice());
        }

        return;
    }
}
