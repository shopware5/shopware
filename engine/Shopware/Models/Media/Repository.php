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

namespace Shopware\Models\Media;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\OrderBy;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * The media repository used for the media manager backend module.
 *
 * The repository is responsible to load the media data into the models.
 *
 * @extends ModelRepository<Media>
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to access a list of media
     *
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query<Media>
     */
    public function getMediaListQuery($filter = null, $orderBy = null, $limit = null, $offset = null)
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
     *
     * @param array<string, string>|array<array{property: string, value: mixed, expression?: string}>|null $filter
     * @param string|array<array{property: string, direction: string}>|null                                $orderBy
     *
     * @return QueryBuilder
     */
    public function getMediaListQueryBuilder($filter, $orderBy)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('media', 'attribute')
                ->from(Media::class, 'media')
                ->leftJoin('media.attribute', 'attribute');
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
     * @param int                       $albumId
     * @param string|null               $filter
     * @param array|string|OrderBy|null $orderBy
     * @param int|null                  $offset
     * @param int|null                  $limit
     * @param array|null                $validTypes
     *
     * @return Query<Media>
     */
    public function getAlbumMediaQuery($albumId, $filter = null, $orderBy = null, $offset = null, $limit = null, $validTypes = null)
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
     *
     * @param int                 $albumId
     * @param string|null         $filter
     * @param string|OrderBy|null $orderBy
     * @param array|null          $validTypes
     *
     * @return QueryBuilder
     */
    public function getAlbumMediaQueryBuilder($albumId, $filter = null, $orderBy = null, $validTypes = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select('media')
                ->from(Media::class, 'media');
        if (!empty($albumId)) {
            $builder->where('media.albumId = :albumId')->setParameter('albumId', $albumId);
        }

        if ($filter !== null) {
            $builder->andWhere('(media.name LIKE ?1 OR media.description LIKE ?1)');
            $builder->setParameter(1, '%' . $filter . '%');
        }

        // Filter for file types
        if ($validTypes !== null && \is_array($validTypes) && !empty($validTypes) && !empty(array_filter($validTypes))) {
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
     *
     * @param int $albumId
     *
     * @return Query<Album>
     */
    public function getAlbumWithSettingsQuery($albumId)
    {
        $builder = $this->getAlbumWithSettingsQueryBuilder($albumId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAlbumWithSettingsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $albumId
     *
     * @return QueryBuilder
     */
    public function getAlbumWithSettingsQueryBuilder($albumId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['album', 'settings'])
                ->from(Album::class, 'album')
                ->leftJoin('album.settings', 'settings')
                ->where('album.id = ?1')
                ->setParameter(1, $albumId);

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects the media model by path
     *
     * @param string $path
     *
     * @return Query<Media>
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
     * @param string $path
     *
     * @return QueryBuilder
     */
    public function getMediaByPathQueryBuilder($path)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['media']);
        $builder->from(Media::class, 'media')
                ->where('media.path = ?1')
                ->setParameter(1, $path);

        return $builder;
    }
}
