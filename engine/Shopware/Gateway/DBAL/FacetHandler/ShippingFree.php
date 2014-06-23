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

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

/**
 * @package Shopware\Gateway\DBAL\FacetHandler
 */
class ShippingFree implements DBAL
{
    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param Facet|Facet\ShippingFree $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('orderBy');

        $query->resetQueryPart('groupBy');

        $query->select(array(
            'COUNT(DISTINCT products.id) as total'
        ));

        $query->andWhere('variants.shippingfree = 1');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);

        $facet->setTotal($total);

        if ($criteria->getCondition('shipping_free')) {
            $facet->setIsFiltered(true);
        }

        return $facet;
    }

    /**
     * Checks if the passed facet can be handled by this class.
     * @param Facet $facet
     * @return bool
     */
    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\ShippingFree);
    }

}