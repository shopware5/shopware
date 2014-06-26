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

namespace Shopware\Bundle\SearchBundle\DBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\SearchBundle\DBAL\PriceHelper;
use Shopware\Bundle\SearchBundle\Sorting;
use Shopware\Bundle\SearchBundle\Condition;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\DBAL\ConditionHandlerInterface;

/**
 * @package Shopware\Bundle\SearchBundle\DBAL\ConditionHandler
 */
class CoreConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @param PriceHelper $priceHelper
     */
    function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * Checks if the passed condition class is supported
     * by the core query generator.
     *
     * @param ConditionInterface $condition
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        switch (true) {
            case ($condition instanceof Condition\CategoryCondition):
                return true;

            case ($condition instanceof Condition\ManufacturerCondition):
                return true;

            case ($condition instanceof Condition\PriceCondition):
                return true;

            case ($condition instanceof Condition\PropertyCondition):
                return true;

            case ($condition instanceof Condition\CustomerGroupCondition):
                return true;

            case ($condition instanceof Condition\ShippingFreeCondition):
                return true;

            case ($condition instanceof Condition\ImmediateDeliveryCondition):
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
     * @param ConditionInterface $condition
     * @param QueryBuilder $query
     * @param Struct\Context $context
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        Struct\Context $context
    ) {
        switch (true) {
            case ($condition instanceof Condition\CategoryCondition):
                $this->addCategoryCondition($query, $condition);
                break;

            case ($condition instanceof Condition\ManufacturerCondition):
                $this->addManufacturerCondition($query, $condition);
                break;

            case ($condition instanceof Condition\PriceCondition):
                $this->addPriceCondition($query, $condition, $context);
                break;

            case ($condition instanceof Condition\PropertyCondition):
                $this->addPropertyCondition($query, $condition);
                break;

            case ($condition instanceof Condition\CustomerGroupCondition):
                $this->addCustomerGroupCondition($query, $condition);
                break;

            case ($condition instanceof Condition\ShippingFreeCondition):
                $this->addShippingFreeCondition($query);
                break;

            case ($condition instanceof Condition\ImmediateDeliveryCondition):
                $this->addImmediateDeliveryCondition($query);
                break;
        }
    }

    /**
     * Checks if the passed sorting class is supported by this handler.
     *
     * @param SortingInterface $sorting
     * @return bool
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        switch (true) {
            case ($sorting instanceof Sorting\ReleaseDateSorting):
                return true;

            case ($sorting instanceof Sorting\PopularitySorting):
                return true;

            case ($sorting instanceof Sorting\PriceSorting):
                return true;

            case ($sorting instanceof Sorting\DescriptionSorting):
                return true;

            default:
                return false;
        }
    }

    /**
     * Extends the passed query builder object with the passed sorting condition.
     *
     * @param SortingInterface $sorting
     * @param QueryBuilder $query
     * @param Struct\Context $context
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        Struct\Context $context
    ) {
        switch (true) {
            case ($sorting instanceof Sorting\ReleaseDateSorting):
                $this->addReleaseSorting($query, $sorting);
                break;

            case ($sorting instanceof Sorting\PopularitySorting):
                $this->addPopularitySorting($query, $sorting);
                break;

            case ($sorting instanceof Sorting\PriceSorting):
                $this->addPriceSorting($query, $sorting, $context);
                break;

            case ($sorting instanceof Sorting\DescriptionSorting):
                $this->addDescriptionSorting($query, $sorting);

                break;
        }
    }

    /**
     * Extends the query with an customer group check.
     * This check filters all products which are locked for the passed customer group.
     *
     * @param QueryBuilder $query
     * @param Condition\CustomerGroupCondition $customerGroup
     */
    private function addCustomerGroupCondition(QueryBuilder $query, Condition\CustomerGroupCondition $customerGroup)
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
     */
    public function addShippingFreeCondition(QueryBuilder $query)
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
     * @param Condition\PropertyCondition $property
     */
    private function addPropertyCondition(QueryBuilder $query, Condition\PropertyCondition $property)
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
     * @param Condition\CategoryCondition $category
     */
    private function addCategoryCondition(
        QueryBuilder $query,
        Condition\CategoryCondition $category
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
     * @param Condition\ManufacturerCondition $manufacturer
     */
    private function addManufacturerCondition(QueryBuilder $query, Condition\ManufacturerCondition $manufacturer)
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
     * @param Condition\PriceCondition $price
     * @param Struct\Context $context
     */
    private function addPriceCondition(
        QueryBuilder $query,
        Condition\PriceCondition $price,
        Struct\Context $context
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
     * @param QueryBuilder $query
     */
    private function addImmediateDeliveryCondition(QueryBuilder $query)
    {
        $query->andWhere('variants.instock >= variants.minpurchase');
    }

    /**
     * Adds an order by condition to the passed query.
     * The search result will be sorted by the product description.
     *
     * @param QueryBuilder $query
     * @param Sorting\ReleaseDateSorting $sorting
     */
    private function addDescriptionSorting(QueryBuilder $query, Sorting\ReleaseDateSorting $sorting)
    {
        $query->addOrderBy('products.name', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

    /**
     * Adds an order by condition to the passed query.
     * The search result will be sorted by the cheapest or highest price.
     *
     * @param QueryBuilder $query
     * @param Sorting\PriceSorting $sorting
     * @param Struct\Context $context
     */
    private function addPriceSorting(
        QueryBuilder $query,
        Sorting\PriceSorting $sorting,
        Struct\Context $context
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
     * @param Sorting\ReleaseDateSorting $sorting
     */
    private function addPopularitySorting(QueryBuilder $query, Sorting\ReleaseDateSorting $sorting)
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
     * @param Sorting\ReleaseDateSorting $sorting
     */
    private function addReleaseSorting(QueryBuilder $query, Sorting\ReleaseDateSorting $sorting)
    {
        $query->addOrderBy('products.datum', $sorting->getDirection())
            ->addOrderBy('products.changetime', $sorting->getDirection())
            ->addOrderBy('products.id', $sorting->getDirection());
    }

}
