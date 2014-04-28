<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

class Category extends DBAL
{
    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @return \Shopware\Gateway\Search\Facet\Category
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria
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
             AND categories.parent = :category
             AND categories.active = 1'
        );

        $query->groupBy('product_categories.categoryID');

        if (!$query->getParameter(':category')) {
            $query->setParameter(':category', 1, \PDO::PARAM_INT);
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Category */
        $facet->categories = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Category);
    }
}