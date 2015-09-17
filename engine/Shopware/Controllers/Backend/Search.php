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

/**
 * Backend search controller
 *
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
        $entity = $this->Request()->getParam('entity', null);
        $ids    = $this->Request()->getParam('ids', []);
        $id     = $this->Request()->getParam('id', null);
        $term   = $this->Request()->getParam('query', null);
        $offset = $this->Request()->getParam('start', 0);
        $limit  = $this->Request()->getParam('limit', 20);

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
        $data  = $pagination->getIterator()->getArrayCopy();

        $data = $this->hydrateSearchResult($entity, $data);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => $pagination->count()
        ]);
    }

    /**
     * @param string $entity
     * @param array[] $data
     * @return array[]
     */
    private function hydrateSearchResult($entity, $data)
    {
        switch ($entity) {
            case 'Shopware\Models\Category\Category':
                $data = $this->resolveCategoryPath($data);
                break;
        }
        return $data;
    }

    /**
     * @param string $entity
     * @return string[]
     */
    private function getEntitySearchFields($entity)
    {
        /** @var \Shopware\Components\Model\ModelManager $entityManager */
        $entityManager = $this->get('models');
        $metaData = $entityManager->getClassMetadata($entity);
        return $metaData->getFieldNames();
    }

    /**
     * @param $entity
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createEntitySearchQuery($entity)
    {
        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $this->get('models')->createQueryBuilder();
        $query->select('entity')
            ->from($entity, 'entity');

        switch ($entity) {
            case 'Shopware\Models\Article\Article':
                $query->select(['entity.id', 'entity.name', 'mainDetail.number'])
                    ->innerJoin('entity.mainDetail', 'mainDetail')
                    ->leftJoin('entity.details', 'details');
                break;

            case 'Shopware\Models\Property\Value':
                if ($groupId = $this->Request()->getParam('groupId')) {
                    $query->andWhere('entity.optionId = :optionId')
                        ->setParameter(':optionId', $this->Request()->getParam('groupId'));
                }
                break;

            case 'Shopware\Models\Property\Option':
                if ($setId = $this->Request()->getParam('setId')) {
                    $query->innerJoin('entity.relations', 'relations', 'WITH', 'relations.groupId = :setId')
                        ->setParameter(':setId', $setId);
                }
                break;
            case 'Shopware\Models\Category\Category':
                $query->andWhere('entity.parent IS NOT NULL')
                    ->addOrderBy('entity.parentId')
                    ->addOrderBy('entity.position');
                break;
        }

        return $query;
    }

    /**
     * @param string $entity
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param string $term
     */
    private function addSearchTermCondition($entity, $query, $term)
    {
        $fields = $this->getEntitySearchFields($entity);
        $where = [];
        foreach ($fields as $field) {
            $field = 'entity.' . $field;
            $where[] = $field . ' LIKE :search';
        }

        foreach ($this->getCustomSearchConditions($entity) as $condition) {
            $where[] = $condition;
        }

        $where = implode(' OR ', $where);
        $query->andWhere('('.$where.')');
        $query->setParameter('search', '%' . $term . '%');
    }

    /**
     * Gets custom search conditions
     *
     * @param string $entity
     * @return array
     */
    private function getCustomSearchConditions($entity)
    {
        $where = [];
        switch ($entity) {
            case 'Shopware\Models\Article\Article':
                $where[] = $this->createCustomFieldToSearchTermCondition('details', 'number');
                break;
            default:
                break;
        }

        return $where;
    }

    /**
     * Creates a custom search term condition
     *
     * @param string $entity
     * @param string $column
     * @return string
     */
    private function createCustomFieldToSearchTermCondition($entity, $column)
    {
        $field = $entity . '.' . $column;
        $where = $field . ' LIKE :search';
        return $where;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param int[] $ids
     */
    private function addIdsCondition($query, $ids)
    {
        $query->andWhere('entity.id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
    }

    /**
     * @param $data
     * @return array[]
     */
    private function resolveCategoryPath($data)
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
     * Sanitizes the passed term and queries the different areas of the search
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->Request()->isPost()) {
            return;
        }

        // Sanitize and clean up the search parameter for later processing
        $search = $this->Request()->get('search');
        $search = strtolower($search);
        $search = trim($search);

        $search = preg_replace("/[^\\w0-9]+/u", " ", $search);
        $search = trim(preg_replace('/\s+/', '%', $search), "%");

        $articles = $this->getArticles($search);
        $customers = $this->getCustomers($search);
        $orders = $this->getOrders($search);

        $this->View()->assign('searchResult', array(
            'articles' => $articles,
            'customers' => $customers,
            'orders' => $orders
        ));
    }

    /**
     * Queries the articles from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getArticles($search)
    {
        $search2 = Shopware()->Db()->quote("$search%");
        $search = Shopware()->Db()->quote("%$search%");

        $sql = "
            SELECT DISTINCT
                a.id,
                a.name,
                a.description_long,
                a.description,
                IFNULL(d.ordernumber, m.ordernumber) as ordernumber
            FROM s_articles as a
            JOIN s_articles_details as m
            ON m.id = a.main_detail_id
            LEFT JOIN s_articles_details as d
            ON a.id = d.articleID
            AND d.ordernumber LIKE $search2
            LEFT JOIN s_articles_translations AS t
            ON a.id=t.articleID
            LEFT JOIN s_articles_supplier AS s
            ON a.supplierID=s.id
            WHERE ( a.name LIKE $search
                OR t.name LIKE $search
                OR s.name LIKE $search
                OR d.id IS NOT NULL
            )
        ";
        $sql = Shopware()->Db()->limit($sql, 5);
        return Shopware()->Db()->fetchAll($sql);
    }

    /**
     * Queries the customers from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getCustomers($search)
    {
        $search2 = Shopware()->Db()->quote("$search%");
        $search = Shopware()->Db()->quote("%$search%");

        $sql = "
            SELECT userID as id,
            IF(b.company != '', b.company, CONCAT(b.firstname, ' ', b.lastname)) as name,
            CONCAT(street, ' ', zipcode, ' ', city) as description
            FROM s_user_billingaddress b, s_user u
            WHERE (
                email LIKE $search
                OR customernumber LIKE $search2
                OR TRIM(CONCAT(company,' ', department)) LIKE $search
                OR TRIM(CONCAT(firstname,' ',lastname)) LIKE $search
            )
            AND u.id = b.userID
            GROUP BY id
            ORDER BY name ASC
        ";

        $sql = Shopware()->Db()->limit($sql, 5);
        $result = Shopware()->Db()->fetchAll($sql);

        return $result;
    }

    /**
     * Queries the orders from the database based on the passed search term
     *
     * @param $search
     * @return array
     */
    public function getOrders($search)
    {
        $search = Shopware()->Db()->quote("$search%");

        $sql = "
            SELECT
                o.id,
                o.ordernumber as name,
                o.userID,
                o.invoice_amount as totalAmount,
                o.transactionID,
                o.status,
                o.cleared,
                d.type,
                d.docID,
                CONCAT(
                    IF(b.company != '', b.company, CONCAT(b.firstname, ' ', b.lastname)),
                    ', ',
                    p.description
                ) as description
            FROM s_order o
            LEFT JOIN s_order_documents d
            ON d.orderID=o.id AND docID != '0'
            LEFT JOIN s_order_billingaddress b
            ON o.id=b.orderID
            LEFT JOIN s_core_paymentmeans p
            ON o.paymentID = p.id
            WHERE o.id != '0'
            AND (o.ordernumber LIKE $search
            OR o.transactionID LIKE $search
            OR docID LIKE $search)
            GROUP BY o.id
            ORDER BY o.ordertime DESC
        ";
        $sql = Shopware()->Db()->limit($sql, 5);
        return Shopware()->Db()->fetchAll($sql);
    }

    /**
     * @param $builder
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function getPaginator($builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        /** @var \Shopware\Components\Model\ModelManager $entityManager */
        $entityManager = $this->get('models');
        $pagination = $entityManager->createPaginator($query);
        return $pagination;
    }

    /**
     * @param $ids
     */
    private function getCategories($ids)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'description'])
            ->from('s_categories', 'category')
            ->where('category.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
