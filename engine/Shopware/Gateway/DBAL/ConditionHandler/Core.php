<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\DBAL\SearchPriceHelper;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;

/**
 * @package Shopware\Gateway\DBAL\ConditionHandler
 */
class Core implements DBAL
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

            case ($condition instanceof Condition\ShippingFree):
                return true;

            default:
                return false;
        }
    }

    /**
     * Called if the supportsCondition function returns true.
     * This function extends the passed query builder object with the passed
     * condition.
     *
     * @param Condition $condition
     * @param QueryBuilder $query
     * @param \Shopware\Struct\Context $context
     */
    public function generateCondition(
        Condition $condition,
        QueryBuilder $query,
        Context $context
    ) {
        switch (true) {
            case ($condition instanceof Condition\Category):
                $this->addCategoryCondition($query, $condition);
                break;

            case ($condition instanceof Condition\Manufacturer):
                $this->addManufacturerCondition($query, $condition);
                break;

            case ($condition instanceof Condition\Price):
                $this->addPriceCondition($query, $condition, $context);
                break;

            case ($condition instanceof Condition\Property):
                $this->addPropertyCondition($query, $condition);
                break;

            case ($condition instanceof Condition\CustomerGroup):
                $this->addCustomerGroupCondition($query, $condition);
                break;

            case ($condition instanceof Condition\ShippingFree):
                $this->addShippingFreeCondition($query, $condition);
                break;
        }
    }

    /**
     * Checks if the passed sorting class is supported by this handler.
     *
     * @param Sorting $sorting
     * @return bool
     */
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

    /**
     * Extends the passed query builder object with the passed sorting condition.
     *
     * @param Sorting $sorting
     * @param QueryBuilder $query
     * @param \Shopware\Struct\Context $context
     */
    public function generateSorting(
        Sorting $sorting,
        QueryBuilder $query,
        Context $context
    ) {
        switch (true) {
            case ($sorting instanceof Sorting\ReleaseDate):
                $this->addReleaseSorting($query, $sorting);
                break;

            case ($sorting instanceof Sorting\Popularity):
                $this->addPopularitySorting($query, $sorting);
                break;

            /**@var $sorting Sorting\Price */
            case ($sorting instanceof Sorting\Price):
                $this->addPriceSorting($query, $sorting, $context);
                break;

            /**@var $sorting Sorting\Description */
            case ($sorting instanceof Sorting\Description):
                $this->addDescriptionSorting($query, $sorting);

                break;
        }
    }

    /**
     * Extends the query with an customer group check.
     * This check filters all products which are locked for the passed customer group.
     *
     * @param QueryBuilder $query
     * @param Condition\CustomerGroup $customerGroup
     */
    private function addCustomerGroupCondition(QueryBuilder $query, Condition\CustomerGroup $customerGroup)
    {
        $query->leftJoin(
            'products',
            's_articles_avoid_customergroups',
            'avoidCustomers',
            'avoidCustomers.articleID = products.id
             AND avoidCustomers.customerGroupId IN (:customerGroupIds)'
        );

        $query->setParameter(
            ':customerGroupIds',
            $customerGroup->getCustomerGroupIds(),
            Connection::PARAM_INT_ARRAY
        );

        $query->andWhere('avoidCustomers.articleID IS NULL');
    }

    /**
     * Extends the query that only products with the shippingfree flag are selected.
     *
     * @param QueryBuilder $query
     * @param Condition\ShippingFree $condition
     */
    public function addShippingFreeCondition(QueryBuilder $query, Condition\ShippingFree $condition)
    {
        $query->andWhere('variants.shippingfree = 1');
    }

    /**
     * Extends the query with a product property condition.
     * The passed property condition contains an array of multiple s_filter_values ids
     * which has to be assigned on the product.
     *
     * The function adds for each id an additional inner join on the s_filter_articles.
     *
     * @param QueryBuilder $query
     * @param Condition\Property $property
     */
    private function addPropertyCondition(QueryBuilder $query, Condition\Property $property)
    {
        foreach ($property->getValueIds() as $value) {
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

    /**
     * Extends the query with a category condition.
     * The passed category condition contains an array of multiple category ids.
     * The searched product has to be in one of the passed categories.
     *
     * @param QueryBuilder $query
     * @param Condition\Category $category
     */
    private function addCategoryCondition(
        QueryBuilder $query,
        Condition\Category $category
    ) {
        $query->innerJoin(
            'products',
            's_articles_categories_ro',
            'product_categories',
            'product_categories.articleID = products.id
             AND product_categories.categoryID IN (:category)'
        );

        $query->setParameter(
            ':category',
            $category->getCategoryIds(),
            Connection::PARAM_INT_ARRAY
        );
    }

    /**
     * Extends the query with a manufacturer condition.
     * The passed manufacturer condition contains an array of manufacturer ids.
     * The searched products have to be assigned on one of the passed manufacturers.
     *
     * @param QueryBuilder $query
     * @param Condition\Manufacturer $manufacturer
     */
    private function addManufacturerCondition(QueryBuilder $query, Condition\Manufacturer $manufacturer)
    {
        $query->innerJoin(
            'products',
            's_articles_supplier',
            'manufacturers',
            'manufacturers.id = products.supplierID
             AND products.supplierID IN (:manufacturer)'
        );

        $query->setParameter(
            ':manufacturer',
            $manufacturer->getManufacturerIds(),
            Connection::PARAM_INT_ARRAY
        );
    }

    /**
     * Extends the query with a price range condition.
     * The passed price condition contains a min and max value of the filtered price.
     * Searched products should have a price within this range.
     *
     * @param QueryBuilder $query
     * @param Condition\Price $price
     * @param \Shopware\Struct\Context $context
     */
    private function addPriceCondition(
        QueryBuilder $query,
        Condition\Price $price,
        Context $context
    ) {
        $selection = $this->priceHelper->getCheapestPriceSelection(
            $context->getCurrentCustomerGroup()
        );

        $this->priceHelper->joinPrices(
            $query,
            $context->getCurrentCustomerGroup(),
            $context->getFallbackCustomerGroup()
        );

        $query->andHaving($selection . ' BETWEEN :priceMin AND :priceMax');

        $query->setParameter(':priceMin', $price->getMinPrice())
            ->setParameter(':priceMax', $price->getMaxPrice());
    }

    /**
     * Adds an order by condition to the passed query.
     * The search result will be sorted by the product description.
     *
     * @param QueryBuilder $query
     * @param Sorting\ReleaseDate $sorting
     */
    private function addDescriptionSorting(QueryBuilder $query, Sorting\ReleaseDate $sorting)
    {
        $query->addOrderBy('products.name', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

    /**
     * Adds an order by condition to the passed query.
     * The search result will be sorted by the cheapest or highest price.
     *
     * @param QueryBuilder $query
     * @param Sorting\Price $sorting
     * @param \Shopware\Struct\Context $context
     */
    private function addPriceSorting(
        QueryBuilder $query,
        Sorting\Price $sorting,
        Context $context
    ) {
        $selection = $this->priceHelper->getCheapestPriceSelection(
            $context->getCurrentCustomerGroup()
        );

        $this->priceHelper->joinPrices(
            $query,
            $context->getCurrentCustomerGroup(),
            $context->getFallbackCustomerGroup()
        );

        $query->addSelect($selection . ' as cheapest_price');

        $query->addOrderBy('cheapest_price', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

    /**
     * Adds an order by condition to the query.
     * The search result will be sorted by the popularity of the products.
     *
     * @param QueryBuilder $query
     * @param Sorting\ReleaseDate $sorting
     */
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

    /**
     * Adds an order by condition to the query.
     * The search result will be sorted by the release date and the change date of the product.
     *
     * @param QueryBuilder $query
     * @param Sorting\ReleaseDate $sorting
     */
    private function addReleaseSorting(QueryBuilder $query, Sorting\ReleaseDate $sorting)
    {
        $query->addOrderBy('products.datum', $sorting->getDirection())
            ->addOrderBy('products.changetime', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

}
