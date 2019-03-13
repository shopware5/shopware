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

namespace Shopware\Models\Banner;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the banner model (Shopware\Models\Banner\Banner).
 * <br>
 * The banner model repository is responsible to load all banner data.
 */
class Repository extends ModelRepository
{
    /**
     * Loads all banners. The $filter parameter can
     * be used to narrow the selection down to a category id.
     *
     * @param int|null $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBanners($filter = null)
    {
        $builder = $this->getBannerMainQuery($filter);

        return $builder->getQuery();
    }

    /**
     * Returns all banners for a given category which are still
     * valid including liveshopping banners.
     * The amount of returned banners can be with the $limit parameter.
     *
     * @param int|null $filter    Category ID
     * @param int      $limit     Limit
     * @param bool     $randomize
     */
    public function getAllActiveBanners($filter = null, $limit = 0, $randomize = false)
    {
        $builder = $this->getBannerMainQuery($filter);
        $today = new \DateTime();

        $builder->andWhere('(banner.validFrom <= ?3 OR (banner.validFrom = ?4 OR banner.validFrom IS NULL))')
            ->setParameter(3, $today)
            ->setParameter(4, null);

        $builder->andWhere('(banner.validTo >= ?5 OR (banner.validTo = ?6 OR banner.validTo IS NULL))')
            ->setParameter(5, $today)
            ->setParameter(6, null);

        $ids = $this->getBannerIds($filter, $limit);
        if (!count($ids)) {
            return false;
        }

        $builder->andWhere('banner.id IN (?7)')
            ->setParameter(7, $ids, Connection::PARAM_INT_ARRAY);

        return $builder->getQuery();
    }

    /**
     * Loads all banners without any live shopping banners. The $filter parameter can
     * be used to narrow the selection down to a category id.
     * If the second parameter is set to false only banners which are active will be returned.
     *
     * @param int|null $filter
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getBannerMainQuery($filter = null)
    {
        $builder = $this->createQueryBuilder('banner');
        $builder->select(['banner', 'attribute'])
            ->leftJoin('banner.attribute', 'attribute');
        if ($filter !== null || !empty($filter)) {
            // Filter the displayed columns with the passed filter
            $builder->andWhere('banner.categoryId = ?1')
                ->setParameter(1, $filter);
        }

        return $builder;
    }

    /**
     * @param int $categoryId
     * @param int $limit
     *
     * @return array
     */
    public function getBannerIds($categoryId, $limit = 0)
    {
        $builder = $this->createQueryBuilder('banner');
        $today = new \DateTime();

        $builder->andWhere('(banner.validFrom <= ?3 OR (banner.validFrom = ?4 OR banner.validFrom IS NULL))')
                ->setParameter(3, $today)
                ->setParameter(4, null);

        $builder->andWhere('(banner.validTo >= ?5 OR (banner.validTo = ?6 OR banner.validTo IS NULL))')
                ->setParameter(5, $today)
                ->setParameter(6, null);

        $builder->select(['banner.id as id'])
            ->andWhere('banner.categoryId = ?1')
            ->setParameter(1, $categoryId);
        $retval = [];
        $data = $builder->getQuery()->getArrayResult();
        foreach ($data as $id) {
            $retval[] = $id['id'];
        }
        shuffle($retval);

        if ($limit > 0) {
            $retval = array_slice($retval, 0, $limit);
        }

        return $retval;
    }
}
