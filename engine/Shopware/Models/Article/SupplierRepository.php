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

namespace Shopware\Models\Article;

use Shopware\Components\Model\ModelRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Repository class for Supplier entity
 */
class SupplierRepository extends ModelRepository
{
    /**
     * Query to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersQuery($offset = null, $limit = null)
    {
        return $this->getFriendlyUrlSuppliersBuilder($offset, $limit)->getQuery();
    }

    /**
     * Query builder to fetch all suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersBuilder($offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('supplier')
            ->select(array('supplier.id'));

        if ($limit != null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Query to fetch the number of suppliers that can be used
     * to generate friendly routes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFriendlyUrlSuppliersCountQueryBuilder()
    {
        return $this->createQueryBuilder('supplier')
                ->select('COUNT(DISTINCT supplier.id)');
    }
}
