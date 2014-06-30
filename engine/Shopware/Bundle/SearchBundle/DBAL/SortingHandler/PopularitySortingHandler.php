<?php

namespace Shopware\Bundle\SearchBundle\DBAL\SortingHandler;

use Shopware\Bundle\SearchBundle\DBAL\SortingHandlerInterface;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Components\Model\DBAL\QueryBuilder;

class PopularitySortingHandler implements SortingHandlerInterface
{
    /**
     * Checks if the passed sorting can be handled by this class
     * @param SortingInterface $sorting
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof PopularitySorting);
    }

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param SortingInterface|PopularitySorting $sorting
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        Context $context
    ) {
        if (!$query->includesTable('s_articles_top_seller')) {
            $query->leftJoin(
                'product',
                's_articles_top_seller_ro',
                'topSeller',
                'topSeller.article_id = product.id'
            );
        }

        $query->addOrderBy('topSeller.sales', $sorting->getDirection())
            ->addOrderBy('topSeller.article_id', $sorting->getDirection());
    }

}
