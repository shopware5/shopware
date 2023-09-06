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

namespace Shopware\Components\MultiEdit\Resource\Product;

use Doctrine\ORM\Query;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;

/**
 * The filter class will search for products matching a given filter
 */
class Filter
{
    /**
     * Reference to an instance of the DqlHelper
     *
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * @param DqlHelper $dqlHelper
     */
    public function __construct($dqlHelper)
    {
        $this->dqlHelper = $dqlHelper;
    }

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * Returns a string representation of a given filterArray
     *
     * @param array $filterArray
     *
     * @return string
     */
    public function filterArrayToString($filterArray)
    {
        return implode(' ', array_map(function ($filter) {
            return $filter['token'];
        }, $filterArray));
    }

    /**
     * Builds the actual query for the token list
     *
     * @param array      $tokens
     * @param int|null   $offset
     * @param int|null   $limit
     * @param array|null $orderBy
     *
     * @return Query<Detail>
     */
    public function getFilterQuery($tokens, $offset = null, $limit = null, $orderBy = null)
    {
        $builder = $this->getFilterQueryBuilder($tokens, $orderBy);
        if ($offset) {
            $builder->setFirstResult($offset);
        }
        if ($limit) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Returns the basic filter query builder, with all the rules (tokens) applied
     *
     * @param array $tokens
     * @param array $orderBy
     *
     * @return QueryBuilder
     */
    public function getFilterQueryBuilder($tokens, $orderBy)
    {
        $joinEntities = $this->getDqlHelper()->getJoinColumns($tokens);
        $orderByInfo = null;

        if (isset($orderBy['property'])) {
            $orderByInfo = $this->getDqlHelper()->getColumnInfoByAlias($orderBy['property']);
            if ($orderByInfo) {
                $entity = $this->getDqlHelper()->getEntityForPrefix(strtolower($orderByInfo['entity']));
                $joinEntities[$entity] = $entity;
            }
        }
        $joinEntities = $this->filterJoinEntities($joinEntities);

        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder()
                ->select('partial detail.{id}')
                ->from(Detail::class, 'detail')
                // only products with attributes are considered to be valid
                ->innerJoin('detail.attribute', 'attr')
                ->leftJoin('detail.article', 'article');

        foreach ($joinEntities as $entity) {
            $join = $this->getDqlHelper()->getAssociationForEntity($entity);
            if (!\is_string($join)) {
                continue;
            }
            $builder->leftJoin($join, $this->getDqlHelper()->getPrefixForEntity($entity));
        }

        list($dql, $params) = $this->getDqlHelper()->getDqlFromTokens($tokens);

        foreach ($params as $key => $value) {
            $builder->setParameter($key, $value);
        }

        $builder->andWhere($dql);
        if ($orderByInfo) {
            $direction = isset($orderBy['direction']) ? $orderBy['direction'] : 'DESC';
            $field = $orderByInfo['field'];
            $builder->orderBy(strtolower($orderByInfo['entity']) . '.' . $field, $direction);
        } else {
            $builder->orderBy('detail.id', 'DESC');
        }

        return $builder;
    }

    /**
     * Query builder to select a product with its dependencies
     *
     * @param int $detailId
     *
     * @return QueryBuilder
     */
    public function getArticleQueryBuilder($detailId)
    {
        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder();
        $builder->select([
            'partial detail.{id, number}',
            'partial article.{id, name}',
        ])
        ->from(Detail::class, 'detail')
        // ~ ->leftJoin('detail.article', 'article')
        ->where('detail.id = ?1')
        ->setParameter(1, $detailId);

        return $builder;
    }

    /**
     * @param Query<Detail> $query
     *
     * @return array{0: array<int>, 1: int}
     */
    public function getPaginatedResult($query)
    {
        $paginator = Shopware()->Models()->createPaginator($query);
        $paginator->setUseOutputWalkers(true);

        $totalCount = $paginator->count();

        $result = array_map(
            function ($item) {
                return $item->getId();
            },
            iterator_to_array($paginator)
        );

        // Detach currently handled models in order to avoid invalid models later
        $this->getDqlHelper()->getEntityManager()->clear();

        return [$result, $totalCount];
    }

    /**
     * Will return products which match a given filter (tokens)
     *
     * @param array $tokens
     * @param int   $offset
     * @param int   $limit
     * @param array $orderBy
     *
     * @return array{data: array<array<string, mixed>>, total: int}
     */
    public function filter($tokens, $offset, $limit, $orderBy)
    {
        $query = $this->getFilterQuery($tokens, $offset, $limit, $orderBy);
        list($result, $totalCount) = $this->getPaginatedResult($query);

        $products = $this->getDqlHelper()->getProductsForListing($result);

        $sortedData = [];
        foreach ($result as $id) {
            foreach ($products as $key => $row) {
                if ($row['Detail_id'] == $id) {
                    $sortedData[] = $row;
                    unset($products[$key]);
                    break;
                }
            }
        }

        return [
            'data' => $sortedData,
            'total' => $totalCount,
        ];
    }

    private function filterJoinEntities(array $joinEntities): array
    {
        // Remove Article-Entity
        return array_filter(
            $joinEntities,
            function ($item) {
                return $item !== Article::class && $item !== Detail::class;
            }
        );
    }
}
