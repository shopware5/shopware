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

use Shopware\Bundle\SearchBundle\DBAL\FacetHandlerInterface;
use Shopware\Components\Model\DBAL\QueryBuilder;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;

/**
 * @package Shopware\Bundle\SearchBundle\DBAL\FacetHandler
 */
class ImmediateDeliveryFacetHandler implements FacetHandlerInterface
{
    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param FacetInterface|Facet\ShippingFreeFacet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet\ShippingFreeFacet
     */
    public function generateFacet(
        FacetInterface $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(array(
            'COUNT(DISTINCT products.id) as total'
        ));

        $query->andWhere(
            'variants.instock >= variants.minpurchase'
        );

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);

        $facet->setTotal($total);

        if ($criteria->getCondition('immediate_delivery')) {
            $facet->setIsFiltered(true);
        }

        return $facet;
    }

    /**
     * Checks if the passed facet can be handled by this class.
     * @param FacetInterface $facet
     * @return bool
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\ImmediateDeliveryFacet);
    }

}