<?php

namespace Shopware\Gateway\DBAL\QueryGenerator;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\DBAL\Search;
use Shopware\Gateway\DBAL\SearchPriceHelper;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;

class CoreGenerator extends DBAL
{
    /**
     * @var SearchPriceHelper
     */
    private $priceHelper;

    /**
     * @param SearchPriceHelper $priceHelper
     */
    function __construct(SearchPriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * Checks if the passed condition class is supported
     * by the core query generator.
     *
     * @param Condition $condition
     * @return bool
     */
    public function supportsCondition(Condition $condition)
    {
        switch (true) {
            case ($condition instanceof Condition\Category):
                return true;

            case ($condition instanceof Condition\Manufacturer):
                return true;

            case ($condition instanceof Condition\Price):
                return true;

            case ($condition instanceof Condition\Property):
                return true;

            case ($condition instanceof Condition\CustomerGroup):
                return true;

            default:
                return false;
        }
    }

    public function generateCondition(Condition $condition, QueryBuilder $query)
    {
        switch (true) {
            case ($condition instanceof Condition\Category):
                $this->addCategoryCondition($query, $condition);
                break;

            case ($condition instanceof Condition\Manufacturer):
                $this->addManufacturerCondition($query, $condition);
                break;

            case ($condition instanceof Condition\Price):
                $this->addPriceCondition($query, $condition);
                break;

            case ($condition instanceof Condition\Property):
                $this->addPropertyCondition($query, $condition);
                break;

            case ($condition instanceof Condition\CustomerGroup):
                $this->addCustomerGroupCondition($query, $condition);
        }
    }

    public function supportsSorting(Sorting $sorting)
    {
        switch (true) {
            case ($sorting instanceof Sorting\ReleaseDate):
                return true;

            case ($sorting instanceof Sorting\Popularity):
                return true;

            case ($sorting instanceof Sorting\Price):
                return true;

            case ($sorting instanceof Sorting\Description):
                return true;

            default:
                return false;
        }
    }

    public function generateSorting(Sorting $sorting, QueryBuilder $query)
    {
        switch (true) {
            case ($sorting instanceof Sorting\ReleaseDate):
                $this->addReleaseSorting($query, $sorting);
                break;

            case ($sorting instanceof Sorting\Popularity):
                $this->addPopularitySorting($query, $sorting);
                break;

            /**@var $sorting Sorting\Price */
            case ($sorting instanceof Sorting\Price):
                $this->addPriceSorting($query, $sorting);
                break;

            /**@var $sorting Sorting\Description */
            case ($sorting instanceof Sorting\Description):
                $this->addDescriptionSorting($query, $sorting);

                break;
        }
    }


    private function addCustomerGroupCondition(QueryBuilder $query, Condition\CustomerGroup $customerGroup)
    {
        $query->leftJoin(
            'products',
            's_articles_avoid_customergroups',
            'avoidCustomers',
            'avoidCustomers.articleID = products.id
             AND avoidCustomers.customerGroupId = :customerGroupId'
        );

        $query->setParameter(':customerGroupId', $customerGroup->id);

        $query->andWhere('avoidCustomers.articleID IS NULL');
    }

    private function addPropertyCondition(QueryBuilder $query, Condition\Property $property)
    {
        foreach ($property->values as $value) {
            $key = 'value' . $value;

            $query->innerJoin(
                'products',
                's_filter_articles',
                $key,
                'products.id = ' . $key . '.articleID
                 AND ' . $key . '.valueID = :' . $key
            );

            $query->setParameter(':' . $key, $value, \PDO::PARAM_INT);
        }
    }


    private function addCategoryCondition(QueryBuilder $query, Condition\Category $category)
    {
        $query->innerJoin(
            'products',
            's_articles_categories_ro',
            'product_categories',
            'product_categories.articleID = products.id
             AND product_categories.categoryID = :category'
        );

        $query->setParameter(':category', $category->id, \PDO::PARAM_INT);
    }


    private function addManufacturerCondition(QueryBuilder $query, Condition\Manufacturer $manufacturer)
    {
        $query->innerJoin(
            'products',
            's_articles_supplier',
            'manufacturers',
            'manufacturers.id = products.supplierID
             AND products.supplierID = :manufacturer'
        );

        $query->setParameter(':manufacturer', $manufacturer->id, \PDO::PARAM_INT);
    }


    private function addPriceCondition(QueryBuilder $query, Condition\Price $price)
    {
        $selection = $this->priceHelper->getCheapestPriceSelection(
            $price->currentCustomerGroup
        );

        $this->priceHelper->joinPrices(
            $query,
            $price->currentCustomerGroup,
            $price->fallbackCustomerGroup
        );

        $query->andHaving($selection . ' BETWEEN :priceMin AND :priceMax');

        $query->setParameter(':priceMin', $price->min)
            ->setParameter(':priceMax', $price->max);
    }

    private function addDescriptionSorting(QueryBuilder $query, Sorting\ReleaseDate $sorting)
    {
        $query->addOrderBy('products.name', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }


    private function addPriceSorting(QueryBuilder $query, Sorting\Price $sorting)
    {
        $selection = $this->priceHelper->getCheapestPriceSelection($sorting->currentCustomerGroup);

        $this->priceHelper->joinPrices(
            $query,
            $sorting->currentCustomerGroup,
            $sorting->fallbackCustomerGroup
        );

        $query->addSelect($selection . ' as cheapest_price');

        $query->addOrderBy('cheapest_price', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

    private function addPopularitySorting(QueryBuilder $query, Sorting\ReleaseDate $sorting)
    {
        if (!$query->includesTable('s_articles_top_seller')) {
            $query->leftJoin(
                'products',
                's_articles_top_seller_ro',
                'topSeller',
                'topSeller.article_id = products.id'
            );
        }

        $query->addOrderBy('topSeller.sales', $sorting->getDirection())
            ->addOrderBy('topSeller.article_id', $sorting->getDirection());

    }

    private function addReleaseSorting(QueryBuilder $query, Sorting\ReleaseDate $sorting)
    {
        $query->addOrderBy('products.datum', $sorting->getDirection())
            ->addOrderBy('products.changetime', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

}