<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\CustomModels\SwagAboCommerce;

use Shopware\Components\Model\ModelRepository;

/**
 * Shopware SwagAboCommerce Plugin - Repository
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBundle\Models
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{
    /**
     * Detail query builder.
     *
     * Returns an query builder object which selects a single AboCommerce record with
     * the minimum stack of the AboCommerce associations to prevent an stack overflow
     * in the query.
     * Info: Don't join N:M associations, this woluld blast the query result and doctrine
     * has to iterate milions of records and the function runs into a timeout.
     *
     * @param $articleId
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getDetailQueryBuilder($articleId)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->createQueryBuilder('AboCommerce');
        $builder->addSelect('prices');
        $builder->andWhere('article.id = :articleId')
                ->leftJoin('AboCommerce.article', 'article')
                ->leftJoin('AboCommerce.prices', 'prices')
                ->addOrderBy('prices.durationFrom')
                ->setParameters(array('articleId' => $articleId));

        return $builder;
    }

    /**
     * Listing query builder.
     *
     * Returns an query builder object for a AboCommerce record list.
     * The listing query builder of this repository is used for the backend listing module
     * of this plugin or for the frontend listing.
     *
     * @param array $filter
     * @param array $sort
     * @param int   $offset
     * @param int   $limit
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getListQueryBuilder($filter, $sort, $offset = null, $limit = null)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder*/
        $builder = $this->createQueryBuilder('AboCommerce');

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if ($limit !== null && $offset !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder;
    }
}
