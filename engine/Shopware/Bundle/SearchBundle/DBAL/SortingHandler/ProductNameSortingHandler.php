<?php

namespace Shopware\Bundle\SearchBundle\DBAL\SortingHandler;

use Shopware\Bundle\SearchBundle\DBAL\SortingHandlerInterface;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundle\DBAL\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\DBAL\SortingHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductNameSortingHandler implements SortingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof ProductNameSorting);
    }

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param SortingInterface|ProductNameSorting $sorting
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @return void
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $query->addOrderBy('product.name', $sorting->getDirection())
            ->addOrderBy('product.id', $sorting->getDirection());
    }

}
