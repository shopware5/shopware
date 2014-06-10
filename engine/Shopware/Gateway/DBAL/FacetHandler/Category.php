<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

class Category extends DBAL
{
    /**
     * @var \Shopware\Service\Category
     */
    private $categoryService;

    /**
     * @param \Shopware\Service\Category $categoryService
     */
    function __construct(\Shopware\Service\Category $categoryService)
    {
        $this->categoryService = $categoryService;
    }


    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return \Shopware\Gateway\Search\Facet\Category
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->removeTableInclude('s_categories');

        $query->removeTableInclude('s_articles_categories_ro');

        $query->resetQueryPart('orderBy');

        $query->select(
            array(
                'product_categories.categoryID as id',
                'COUNT(DISTINCT products.id) as total'
            )
        );

        $query->innerJoin(
            'products',
            's_articles_categories_ro',
            'product_categories',
            'product_categories.articleID = products.id'
        );

        $query->innerJoin(
            'product_categories',
            's_categories',
            'categories',
            'categories.id = product_categories.categoryID
             AND categories.parent IN (:category)
             AND categories.active = 1'
        );

        $query->groupBy('product_categories.categoryID');

        if (!$query->getParameter(':category')) {
            $query->setParameter(
                ':category',
                array(1),
                Connection::PARAM_INT_ARRAY
            );
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Category */
        $categories = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        $ids = array_keys($categories);

        $facet->setCategories(
            $this->categoryService->getList($ids, $context)
        );

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Category);
    }
}