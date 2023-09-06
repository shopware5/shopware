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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use PDO;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\CombinedConditionFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CombinedConditionFacetHandler implements PartialFacetHandlerInterface
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    public function __construct(QueryBuilderFactoryInterface $queryBuilderFactory)
    {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof CombinedConditionFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        return $this->getFacet($facet, $reverted, $criteria, $context);
    }

    private function getFacet(
        CombinedConditionFacet $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ): ?BooleanFacetResult {
        $own = clone $reverted;

        if (!$criteria->hasCondition($facet->getName())) {
            foreach ($facet->getConditions() as $condition) {
                $own->addCondition($condition);
            }
        }

        $query = $this->queryBuilderFactory->createQuery($own, $context);
        $query->select('1');
        $query->setMaxResults(1);

        $hasFacet = $query->execute()->fetch(PDO::FETCH_COLUMN);
        if (!$hasFacet) {
            return null;
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $facet->getRequestParameter(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel()
        );
    }
}
