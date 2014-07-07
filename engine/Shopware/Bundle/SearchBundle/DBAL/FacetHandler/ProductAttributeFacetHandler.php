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

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\DBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\SearchBundle\DBAL\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\DBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductAttributeFacetHandler implements FacetHandlerInterface
{
    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param FacetInterface|ProductAttributeFacet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Struct\Context $context
     * @return FacetInterface
     */
    public function generateFacet(
        FacetInterface $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Struct\Context $context
    ) {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(array(
            'COUNT(DISTINCT product.id) as total'
        ));

        switch ($facet->getMode()) {
            case (ProductAttributeFacet::MODE_EMPTY):
                $query->andWhere(
                    "(productAttribute." . $facet->getField() . " IS NULL
                     OR productAttribute." . $facet->getField() . " = '')"
                );
                break;

            case (ProductAttributeFacet::MODE_NOT_EMPTY):
                $query->andWhere(
                    "(productAttribute." . $facet->getField() . " IS NOT NULL
                     OR productAttribute." . $facet->getField() . " != '')"
                );
                break;

            default:
                $query->addSelect('productAttribute.' . $facet->getField())
                    ->orderBy('productAttribute.' . $facet->getField())
                    ->groupBy('productAttribute.' . $facet->getField());

                break;
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $facet->setResult($result);

        return $facet;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof ProductAttributeFacet);
    }

}
