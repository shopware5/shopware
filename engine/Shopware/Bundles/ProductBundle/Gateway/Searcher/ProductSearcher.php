<?php

namespace ProductBundle\Gateway\Searcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Search;

class ProductSearcher extends Search
{
    protected function createQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'product.id as __product_id',
            'variant.id as __variant_id',
            'variant.ordernumber as __variant_ordernumber',
        ]);
        $query->from('s_articles', 'product');
        $query->leftJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id');

        $query->groupBy('product.id');

        return $query;
    }
}