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

namespace Shopware\Models\Premium;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the premium model (Shopware\Models\Premium\Premium).
 * <br>
 * The premium model repository is responsible to load all premium articles.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Function to get all premium-articles and the subshop-name and the article-name
     *
     * @param int         $start
     * @param int         $limit
     * @param string      $order
     * @param string|null $filterValue
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBackendPremiumListQuery($start, $limit, $order, $filterValue = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'premium.id',
            'premium.startPrice as startPrice',
            'premium.orderNumber as orderNumber',
            'premium.orderNumberExport as orderNumberExport',
            'premium.shopId as shopId',
            'subshop.name as subShopName',
            'article.name as name',
            ])
            ->from($this->getEntityName(), 'premium')
            ->leftJoin('premium.shop', 'subshop')
            ->leftJoin('premium.articleDetail', 'detail')
            ->leftJoin('detail.article', 'article');

        if ($filterValue !== null) {
            $builder->where('article.name LIKE ?1')
            ->setParameter(1, '%' . $filterValue . '%');
        }
        if (!empty($order)) {
            $builder->addOrderBy($order);
        }
        $builder->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->getQuery();
    }
}
