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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Shopware\Components\Backend\GlobalSearch;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Category\Category;
use Shopware\Models\Emotion\LandingPage;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;

/**
 * This controller provides the global search in the Shopware backend. The
 * search has the ability to provides search results from the different
 * areas starting from articles to orders
 */
class Shopware_Controllers_Backend_Search extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Generic search action for entities
     */
    public function searchAction()
    {
        $entity = $this->Request()->getParam('entity');
        $ids = $this->Request()->getParam('ids', []);
        $id = $this->Request()->getParam('id');
        $term = $this->Request()->getParam('query');
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);

        $builder = $this->createEntitySearchQuery($entity);

        if (!empty($ids)) {
            $ids = json_decode($ids, true);
            $this->addIdsCondition($builder, $ids);
        } elseif (!empty($id)) {
            $this->addIdsCondition($builder, [$id]);
        } else {
            if (!empty($term)) {
                $this->addSearchTermCondition($entity, $builder, $term);
            }

            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $pagination = $this->getPaginator($builder);
        $data = $pagination->getIterator()->getArrayCopy();

        $data = $this->hydrateSearchResult($entity, $data);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => $pagination->count(),
        ]);
    }

    /**
     * Sanitizes the passed term and queries the different areas of the search
     */
    public function indexAction()
    {
        if (!$this->Request()->isPost()) {
            return;
        }

        // Sanitize and clean up the search parameter for later processing
        $term = mb_strtolower(trim($this->Request()->get('search')));

        $term = preg_replace('/[^\\w0-9]+/u', ' ', $term);
        $term = trim(preg_replace('/\s+/', '%', $term), '%');

        if ($term === '') {
            return;
        }

        $search = $this->container->get(GlobalSearch::class);
        $result = $search->search($term);

        if (!$this->_isAllowed('read', 'article')) {
            $result['articles'] = [];
        }

        if (!$this->_isAllowed('read', 'customer')) {
            $result['customers'] = [];
        }

        if (!$this->_isAllowed('read', 'order')) {
            $result['orders'] = [];
        }

        $this->View()->assign('searchResult', $result);
    }

    /**
     * @param string $entity
     *
     * @return QueryBuilder
     */
    public function createEntitySearchQuery($entity)
    {
        /** @var QueryBuilder $query */
        $query = $this->get(ModelManager::class)->createQueryBuilder();
        $query->select('entity')
            ->from($entity, 'entity');

        switch ($entity) {
            case Product::class:
                $query->select(['entity.id', 'entity.name', 'mainDetail.number'])
                    ->innerJoin('entity.mainDetail', 'mainDetail')
                    ->leftJoin('entity.details', 'details');
                break;

            case Value::class:
                if ($groupId = $this->Request()->getParam('groupId')) {
                    $query->andWhere('entity.optionId = :optionId')
                        ->setParameter(':optionId', $groupId);
                }
                break;

            case Option::class:
                if ($setId = $this->Request()->getParam('setId')) {
                    $query->innerJoin('entity.relations', 'relations', 'WITH', 'relations.groupId = :setId')
                        ->setParameter(':setId', $setId);
                }
                break;

            case Category::class:
                $query->andWhere('entity.parent IS NOT NULL')
                    ->addOrderBy('entity.parentId')
                    ->addOrderBy('entity.position');
                break;

            case LandingPage::class:
                $query->andWhere('entity.isLandingPage = 1');
                break;
        }

        return $query;
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return Paginator
     */
    protected function getPaginator($builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        /** @var ModelManager $entityManager */
        $entityManager = $this->get(ModelManager::class);

        return $entityManager->createPaginator($query);
    }

    /**
     * @param array[] $data
     *
     * @return array[]
     */
    private function hydrateSearchResult(string $entity, array $data): array
    {
        $data = array_map(static function ($row) {
            if (array_key_exists('_score', $row) && array_key_exists(0, $row)) {
                return $row[0];
            }

            return $row;
        }, $data);

        if ($entity === Category::class) {
            $data = $this->resolveCategoryPath($data);
        }

        return $data;
    }

    private function getEntitySearchFields(string $entity): array
    {
        /** @var ModelManager $entityManager */
        $entityManager = $this->get(ModelManager::class);
        $metaData = $entityManager->getClassMetadata($entity);

        $fields = array_filter(
            $metaData->getFieldNames(),
            static function ($field) use ($metaData) {
                $type = $metaData->getTypeOfField($field);

                return in_array($type, ['string', 'text', 'decimal', 'float']);
            }
        );

        if (empty($fields)) {
            return $metaData->getFieldNames();
        }

        return $fields;
    }

    private function addSearchTermCondition(string $entity, QueryBuilder $query, string $term): void
    {
        $fields = $this->getEntitySearchFields($entity);

        $builder = Shopware()->Container()->get('shopware.model.search_builder');

        $fields = array_map(static function ($field) {
            return 'entity.' . $field;
        }, $fields);

        if ($entity === Product::class) {
            $fields[] = 'mainDetail.number';
        }

        $builder->addSearchTerm($query, $term, $fields);
    }

    private function addIdsCondition(QueryBuilder $query, array $ids): void
    {
        $query->andWhere('entity.id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
    }

    /**
     * @return array[]
     */
    private function resolveCategoryPath(array $data): array
    {
        $ids = [];
        foreach ($data as $row) {
            $ids = array_merge($ids, explode('|', $row['path']));
            $ids[] = $row['id'];
        }
        $ids = array_values(array_unique(array_filter($ids)));
        $categories = $this->getCategories($ids);

        foreach ($data as &$row) {
            $parents = array_filter(explode('|', $row['path']));
            $parents = array_reverse($parents);
            $path = [];
            foreach ($parents as $parent) {
                $path[] = $categories[$parent];
            }
            $path[] = $row['name'];
            $row['name'] = implode('>', $path);
        }

        return $data;
    }

    /**
     * @param int[] $ids
     */
    private function getCategories(array $ids): array
    {
        /** @var DBALQueryBuilder $query */
        $query = $this->get(Connection::class)->createQueryBuilder();
        $query->select(['id', 'description'])
            ->from('s_categories', 'category')
            ->where('category.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
