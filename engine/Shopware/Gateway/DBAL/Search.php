<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\Condition;
use Shopware\Gateway\Result;

class Search implements \Shopware\Gateway\Search
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @param ModelManager $entityManager
     */
    function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Condition $condition
     * @return \Shopware\Gateway\Result
     */
    public function search(Condition $condition)
    {
        $products = $this->getProducts($condition);

        $result = new Result();
        $result->setProducts(
            array_column($products, 'ordernumber')
        );
        $sortCondition = $condition->sort;
        $condition->sort = null;

        $result->setTotalCount(
            $this->getTotalCount()
        );

        $result->setSuppliers(
            $this->getSuppliers($condition)
        );

        $result->setProperties(
            $this->getProperties($condition)
        );

        $result->setPrices(
            $this->getPrices($condition)
        );

        $result->setCategories(
            $this->getCategories($condition)
        );

        $condition->sort = $sortCondition;

        return $result;
    }


    /**
     * Returns the aggregates result for the product categories.
     * The returned array contains the category id
     *
     *
     * @param Condition $condition
     * @return array
     */
    private function getCategories(Condition $condition)
    {
        //remove category flag to remove only the categories join in the base query
        $category = $condition->category;
        $condition->category = 0;

        //the query contains now no join condition on the categories tables.
        $query = $this->getBaseQuery($condition);
        $condition->category = $category;

        //requires a straight_join statement.
        //Otherwise the s_articles table is used as first join table, because the active flag
        //has a higher cardinality
        $query->select(array(
            'categories.id',
            'COUNT(product_categories.articleID) as total'
        ));

        $query->resetQueryPart('from');
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->from('s_categories', 'categories');

        $query->innerJoin(
            'categories',
            's_articles_categories_ro',
            'product_categories',
            'product_categories.categoryID = categories.id'
        );

        $query->innerJoin(
            'product_categories',
            's_articles',
            'products',
            'products.id = product_categories.articleID'
        );

        $query->andWhere('categories.path LIKE :category');

        $query->setParameter(':category', '|' . $condition->category . '|%');

        $query->groupBy('categories.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getPrices(Condition $condition)
    {
        //check if the base query is already filtered with price ranges
        if ($condition->price) {

            //in this case aggregate only the filtered price ranges
            $scaled = array($condition->price);
        } else {
            //default price ranges
            $scaled = array(
                array(1, 100),
                array(101, 200),
                array(201, 300),
                array(301, null)
            );
        }

        $priceCondition = $condition->price;
        $condition->price = null;

        $prices = array();
        foreach ($scaled as &$selection) {
            $query = $this->getPriceRangeQuery($condition);

            $query->setParameter(':from', $selection[0]);

            //if no to value set, group all prices which are bigger than :from
            if ($selection[1] === null) {
                $query->setParameter(':to', 999999999);
            } else {
                $query->setParameter(':to', $selection[1]);
            }

            /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
            $statement = $query->execute();

            $prices[] = array(
                'from' => $selection[0],
                'to' => $selection[1],
                'total' => $statement->fetch(\PDO::FETCH_COLUMN)
            );
        }

        $condition->price = $priceCondition;

        return $prices;
    }


    private function getPriceRangeQuery(Condition $condition)
    {
        $query = $this->getBaseQuery($condition);

        $query->resetQueryPart('from');
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(array('COUNT(DISTINCT prices.id) as total'));

        $query->from('s_articles_prices', 'prices')
            ->innerJoin('prices', 's_articles', 'products', 'products.main_detail_id = prices.articledetailsID');

        $query->andWhere('prices.from = 1')
            ->andWhere("prices.pricegroup = 'EK'")
            ->andWhere('prices.price BETWEEN :from AND :to');

        return $query;
    }

    private function getTotalCount()
    {
        return $this->entityManager->getConnection()->executeQuery("SELECT FOUND_ROWS();")
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param Condition $condition
     * @return array
     */
    private function getProperties(Condition $condition)
    {
        $query = $this->getBaseQuery($condition);

        $query->resetQueryPart('from');
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(array(
            'filterValues.id as id',
            'COUNT(DISTINCT products.id) as total'
        ));

        $query->from('s_filter_values', 'filterValues')
            ->innerJoin('filterValues', 's_filter_articles', 'association', 'association.valueID = filterValues.id')
            ->innerJoin('filterValues', 's_articles', 'products', 'products.id = association.articleID')
            ->orderBy('filterValues.optionID', 'ASC')
            ->addOrderBy('filterValues.value', 'ASC')
            ->groupBy('filterValues.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param Condition $condition
     * @return array
     */
    private function getSuppliers(Condition $condition)
    {
        $query = $this->getBaseQuery($condition);

        $query->resetQueryPart('from');
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->from('s_articles_supplier', 'suppliers')
            ->innerJoin('suppliers', 's_articles', 'products', 'suppliers.id = products.supplierID')
            ->select(array(
                'products.supplierID as id',
                'COUNT(products.id) as total'
            ))
            ->orderBy('suppliers.name', 'ASC')
            ->groupBy('suppliers.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param Condition $condition
     * @return array
     */
    private function getProducts(Condition $condition)
    {
        $query = $this->getBaseQuery($condition)
            ->select(array('SQL_CALC_FOUND_ROWS DISTINCT variants.ordernumber'))
            ->setFirstResult($condition->offset)
            ->setMaxResults($condition->limit);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Builds a query which selects the basic data for products.
     *
     * @param Condition $condition
     * @return QueryBuilder
     */
    private function getBaseQuery(Condition $condition)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_articles', 'products');

        switch($condition->sort) {
            //newcomer
            case 1:
                $query->addOrderBy('products.datum', 'DESC')
                    ->addOrderBy('products.id', 'DESC');

                break;

            //popularity
            case 2:
                $query->resetQueryPart('from');

                $query->from('s_articles_top_seller_ro', 'top_seller')
                    ->innerJoin(
                        'top_seller',
                        's_articles',
                        'products',
                        'products.id = top_seller.article_id'
                    );

                $query->addOrderBy('top_seller.sales', 'DESC')
                    ->addOrderBy('top_seller.article_id', 'DESC');

                break;

            //price sorting
            case 3-4:
                $query->resetQueryPart('from');

                $query->from('s_articles_prices', 'prices')
                    ->innerJoin(
                        'prices',
                        's_articles',
                        'products',
                        "products.main_detail_id = prices.articledetailsID
                         AND prices.from = 1
                         AND prices.pricegroup = 'EK'"
                    );

                $direction = 'ASC';
                if ($condition->sort == 4) $direction = 'DESC';

                $query->addOrderBy('prices.price', $direction)
                    ->addOrderBy('prices.articleID', $direction);

                break;

            //product name ascending
            case 5:
                $query->addOrderBy('products.name', 'ASC')
                    ->addOrderBy('products.id', 'ASC');
                break;

            //product name descending
            case 6:
                $query->addOrderBy('products.name', 'DESC')
                    ->addOrderBy('products.id', 'DESC');
                break;

            default:
                $query->addOrderBy('products.datum', 'DESC')
                    ->addOrderBy('products.id', 'DESC');
        }


        $query->innerJoin('products', 's_articles_details', 'variants', 'variants.id = products.main_detail_id')
            ->innerJoin('products', 's_core_tax', 'tax', 'tax.id = products.taxID')
            ->andWhere('products.active = 1')
            ->andWhere('variants.active = 1');

        if ($condition->category) {
            $query->innerJoin(
                'products',
                's_articles_categories_ro',
                'product_categories',
                'product_categories.articleID = products.id
                 AND product_categories.categoryID = :category'
            )
                ->setParameter(':category', $condition->category);

            $query->addGroupBy('products.main_detail_id');
        }

        if ($condition->supplier) {
            $query->andWhere('products.supplierID = :supplier')
                ->setParameter(':supplier', $condition->supplier);
        }

        if ($condition->price) {
            $query->innerJoin('products', 's_articles_prices', 'prices', 'products.main_detail_id = prices.articledetailsID')
                ->andWhere('prices.from = 1')
                ->andWhere("prices.pricegroup = 'EK'")
                ->andWhere('prices.price BETWEEN :from AND :to')
                ->setParameter(':from', $condition->price[0])
                ->setParameter(':to', $condition->price[1]);
        }

        if ($condition->properties) {
            foreach ($condition->properties as $propertyId) {
                $alias = 'property' . $propertyId;

                $query->innerJoin(
                    'products',
                    's_filter_articles',
                    $alias,
                    'products.id = ' . $alias . '.articleID AND ' . $alias . '.valueID = :' . $alias)
                    ->setParameter(':' . $alias, intval($propertyId));
            }

            $query->addGroupBy('products.main_detail_id');
        }
        return $query;
    }

}