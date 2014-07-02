<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle\DBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\DBAL\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\DBAL\PriceHelper;
use Shopware\Bundle\SearchBundle\DBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;

/**
 * @package Shopware\Bundle\SearchBundle\DBAL\FacetHandler
 */
class PriceFacetHandler implements FacetHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @param PriceHelper $priceHelper
     */
    function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\PriceFacet);
    }

    /**
     * @param FacetInterface|Facet\PriceFacet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return FacetInterface
     */
    public function generateFacet(
        FacetInterface $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        /**@var $condition \Shopware\Bundle\SearchBundle\Condition\PriceCondition */
        if ($condition = $criteria->getCondition('price')) {
            $facet->setMinPrice($condition->getMinPrice());
            $facet->setMaxPrice($condition->getMaxPrice());
            return $facet;
        }

        $this->priceHelper->joinPrices(
            $query,
            $context->getCurrentCustomerGroup(),
            $context->getFallbackCustomerGroup()
        );

        $selection = $this->priceHelper->getCheapestPriceSelection(
            $context->getCurrentCustomerGroup()
        );

        $query->select(
            array(
                $selection . ' as cheapest_price'
            )
        );

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $min = $statement->fetch(\PDO::FETCH_COLUMN);

        $query->groupBy('product.id')
            ->orderBy('cheapest_price', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $max = $statement->fetch(\PDO::FETCH_COLUMN);

        $facet->setMinPrice($min);
        $facet->setMaxPrice($max);

        return $facet;
    }
}
