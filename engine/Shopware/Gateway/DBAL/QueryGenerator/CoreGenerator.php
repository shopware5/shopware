<?php

namespace Shopware\Gateway\DBAL\QueryGenerator;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;

class CoreGenerator extends DBAL
{
    private $productSortingFields = array();

    public function supportsCondition(Condition $condition)
    {
        switch (true) {
            case ($condition instanceof Condition\Category):
                return true;

            default:
                return false;
        }
    }


    public function generateCondition(Condition $condition, QueryBuilder $query)
    {
        switch (true) {
            case ($condition instanceof Condition\Category):
                $this->joinCategory($query, $condition);
        }
    }


    public function supportsSorting(Sorting $sorting)
    {
        return parent::supportsSorting($sorting);
    }


    public function generateSorting(Sorting $sorting, QueryBuilder $query)
    {
        parent::generateSorting($sorting, $query);
    }

    private function joinCategory(QueryBuilder $query, Condition\Category $category)
    {
        $query->innerJoin(
            'products',
            's_articles_categories_ro',
            'product_categories',
            'product_categories.articleID = products.id AND product_categories.categoryID = :category'
        );

        $query->innerJoin(
            'product_categories',
            's_categories',
            'categories',
            'categories.id = product_categories.categoryID'
        );

        $query->setParameter(':category', $category->id);
    }

}