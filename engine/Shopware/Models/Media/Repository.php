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

namespace   Shopware\Models\Media;

use Shopware\Components\Model\ModelRepository;

/**
 * The media repository used for the media manager backend module.
 * <br>
 * The repository is responsible to load the media data into the models.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to access a list of media
     * @param $filter
     * @param $orderBy
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getMediaListQuery($filter = null, $orderBy = null, $limit= null, $offset = null)
    {
        $builder = $this->getMediaListQueryBuilder($filter, $orderBy);
        if ($limit !== null && $offset !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMediaListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @param $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMediaListQueryBuilder($filter, $orderBy)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('media')
                ->from('Shopware\Models\Media\Media', 'media');
        if ($filter) {
            $builder->addFilter($filter);
        }
        if ($orderBy) {
            $builder->addOrderBy($orderBy);
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the media of the passed album id.
     * Used for the backend media manager listing of the media.
     *
     * @param      $albumId
     * @param null $filter
     * @param null $orderBy
     * @param null $offset
     * @param null $limit
     * @param null $validTypes
     * @return \Doctrine\ORM\Query
     */
    public function getAlbumMediaQuery($albumId, $filter = null, $orderBy = null, $offset = null, $limit= null, $validTypes = null)
    {
        $builder = $this->getAlbumMediaQueryBuilder($albumId, $filter, $orderBy, $validTypes);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAlbumMediaQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param      $albumId
     * @param null $filter
     * @param null $orderBy
     * @param null $validTypes
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAlbumMediaQueryBuilder($albumId, $filter = null, $orderBy = null, $validTypes = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $builder->select('media')
                ->from('Shopware\Models\Media\Media', 'media');
        if ($albumId != null || $albumId != 0) {
            $builder->where($expr->eq('media.albumId', $albumId));
        }

        if ($filter!== null) {
            $builder->andWhere(
                $expr->orX(
                    $expr->like('media.name', '?1'),
                    $expr->like('media.description', '?1')
                )
            );
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Filter for file types
        if ($validTypes !== null && is_array($validTypes) && !empty($validTypes)) {
            $builder->andWhere('media.extension IN (?2)');
            $builder->setParameter(2, $validTypes);
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        } else {
            $builder->addOrderBy('media.id', 'DESC');
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $albumId
     * @return \Doctrine\ORM\Query
     */
    public function getAlbumWithSettingsQuery($albumId)
    {
        $builder = $this->getAlbumWithSettingsQueryBuilder($albumId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAlbumWithSettingsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $albumId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAlbumWithSettingsQueryBuilder($albumId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('album', 'settings'))
                ->from('Shopware\Models\Media\Album', 'album')
                ->leftJoin('album.settings', 'settings')
                ->where('album.id = ?1')
                ->setParameter(1, $albumId);

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects the media model by path
     *
     * @param $path
     * @return \Doctrine\ORM\Query
     */
    public function getMediaByPathQuery($path)
    {
        $builder = $this->getMediaByPathQueryBuilder($path);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMediaByPathQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $path
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMediaByPathQueryBuilder($path)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('media'));
        $builder->from('Shopware\Models\Media\Media', 'media')
                ->where('media.path = ?1')
                ->setParameter(1, $path);
        return $builder;
    }
}
