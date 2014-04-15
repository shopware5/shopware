<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Facet;

class Category extends DBAL
{
    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     */
    public function generateFacet(Facet $facet, QueryBuilder $query)
    {
        $query->removeTableInclude('s_categories');
        $query->removeTableInclude('s_articles_categories_ro');
        $query->resetQueryPart('groupBy');

        $query->select(array('product_categories.categoryID', 'COUNT(products.id) as total'))
            ->innerJoin(
                'products',
                's_articles_categories_ro',
                'product_categories',
                'product_categories.articleID = products.id'
            )
            ->innerJoin(
                'product_categories',
                's_categories',
                'categories',
                'categories.id = product_categories.categoryID
                 AND categories.parent = :category
                 AND categories.active = 1'
            );

        $query->groupBy('product_categories.categoryID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Category*/
        $facet->categories = $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Category);
    }
}