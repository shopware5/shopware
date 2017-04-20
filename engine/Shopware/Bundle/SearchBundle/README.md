# SearchBundle

- **License**: Dual license AGPL v3 / Proprietary
- **Github Repository**: <https://github.com/ShopwareAG/shopware-4>
- **Issue-Tracker**: <https://issues.shopware.com>

## How to use
The usage of the Shopware\Bundle\SearchBundle\ProductSearch and Shopware\Bundle\SearchBundle\ProductNumberSearch are equal. Only the result object is different.

```
//load or create a context object to define which user context is set
$context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();

$criteria = new \Shopware\Bundle\SearchBundle\Criteria();

//defines to search only products which are assigned to the category with the id 3.
$criteria->addCondition(new \Shopware\Bundle\SearchBundle\Condition\CategoryCondition(array(3));

//defines to generate a price facet, which displays the price range of the search result.
$criteria->addFacet(new \Shopware\Bundle\SearchBundle\Facet\PriceFacet());

//defines to sort by the cheapest price of the products.
$criteria->addSorting(new \Shopware\Bundle\SearchBundle\Sorting\PriceSorting());

//defines to load only an offset of products and not the whole product catalog
$criteria->offset(0);
$criteria->limit(20);

//executes a search request to find only the product numbers
$productNumberResult = Shopware()->Container()->get('shopware_search.product_number_search')->search(
    $criteria,
    $context
);

//executes a search request to find a list of \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct.
$context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
$productResult = Shopware()->Container()->get('shopware_search.product_search')->search(
    $criteria,
    $context
);

```

## How it works
The Shopware\Bundle\SearchBundle provides a ProductNumberSearchInterface which expects a Shopware\Bundle\SearchBundle\Criteria and a Shopware\Bundle\StoreFrontBundle\ShopContextInterface object.

The Criteria class contains the definition which conditions, sortings and facets (terms) the search has to consider.

The ShopContextInterface class contains the current user/shop context like which customer group is active or which language is selected.

This both classes are required for a search request.

The ProductNumberSearchInterface is only the definition of the search API. The ProductNumberSearch is implemented for a specify database platform like Mysql or Elasticsearch.

The implementation of the ProductNumberSearch has to interpret the provided ShopContextInterface and Criteria object to the specify database language.


## Default ProductNumberSearch
Shopware implements a DBAL ProductNumberSearch as default. This implementation of the ProductNumberSearch interprets the provided context and criteria object over associated handler classes.

- Shopware\Bundle\SearchBundleDBAL\SortingHandler
- Shopware\Bundle\SearchBundleDBAL\ConditionHandler
- Shopware\Bundle\SearchBundleDBAL\FacetHandler

Notice: Each term has his own handler class and can only be handled by one handler class.

**Important: This design pattern isn't necessary and couldn't be implemented in other replacements of the ProductNumberSearch like Elasticsearch.**

The DBAL implementation of the ProductNumberSearch provides interfaces for this handler classes, which allows third party developers to extend the default implementation or to overwrite existing handler classes:

- Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface
- Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface
- Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface

## Define own handler classes
Each handler class has to implement on the associated database platform. For example, a handler class which expects a DBAL query builder to extend the search request can't be used in the Elasticsearch ProductNumberSearch.

Additionally handlers can only be implemented if the platform implementation of the ProductNumberSearch supports this concept.

### SortingHandler
A SortingHandler has to implement the following functions:

- ```supportsSorting``` which checks if the provided sorting class can be handled by the handler

- ```generateSorting``` which extends the provided query builder instance with the provided sorting definition.

The following sorting handler adds an inner join condition to the existing query to join the top seller data of each product and sorts the result by the product sales ascending.

```
<?php

class PluginSortingHandler implements \Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface
{
    public function supportsSorting(\Shopware\Bundle\SearchBundle\SortingInterface $sorting)
    {
        return ($sorting instanceof ...);
    }

    public function generateSorting(
        \Shopware\Bundle\SearchBundle\SortingInterface   $sorting,
        \Shopware\Bundle\SearchBundle\DBAL\QueryBuilder  $query,
        \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
    ) {
        $query->innerJoin(
            'product',
            's_articles_top_seller_ro',
            'pluginTopSeller',
            'pluginTopSeller.article_id = product.id'
        );

        $query->addOrderBy('pluginTopSeller.sales', 'ASC');
    }
}
```

### ConditionHandler
A ConditionHandler has to implement the following functions:

- ```supportsCondition``` which checks if the provided condition class can be handled by the handler

- ```generateCondition``` which extends the provided query builder instance with the provided condition definition.

The following condition handler joins the top seller data of the products and selects only products which sold more than 20x times.

```
<?php

class PluginConditionHandler implements \Shopware\Bundle\SearchBundle\DBAL\ConditionHandlerInterface
{
    public function supportsCondition(\Shopware\Bundle\SearchBundle\ConditionInterface $condition)
    {
        return ($condition instanceof ...);
    }

    public function generateCondition(
        \Shopware\Bundle\SearchBundle\ConditionInterface $condition,
        \Shopware\Bundle\SearchBundle\DBAL\QueryBuilder  $query,
        \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
    ) {
        $query->innerJoin(
            'product',
            's_articles_top_seller_ro',
            'pluginTopSeller',
            'pluginTopSeller.article_id = product.id'
        );

        $query->andWhere('pluginTopSeller.sales > 20');
    }
}
```

### FacetHandler
A FacetHandler has to implement the following functions:

- ```supportsFacet``` which checks if the provided facet class can be handled by the handler

- ```generateFacet``` which modifies the passed query builder to select the facet data for the provided facet definition.

The following FacetHandler wants to display the total count of products which sold more than 20x times.

**Notice: The FacetHandler has to load all required data to display the facet in the store front. This includes translations, too.**

```
class PluginFacetHandler implements \Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface
{
    /**
     * @param QueryBuilderFactoryInterface $queryBuilderFactory
     */
    public function __construct(
        \Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    public function supportsFacet(\Shopware\Bundle\SearchBundle\FacetInterface $facet)
    {
        return ($facet instanceof ...);
    }

    public function generateFacet(
        \Shopware\Bundle\SearchBundle\FacetInterface     $facet,
        \Shopware\Bundle\SearchBundle\DBAL\QueryBuilder  $query,
        \Shopware\Bundle\SearchBundle\Criteria           $criteria,
        \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($criteria, $context);

        $query->resetQueryPart('orderBy');

        $query->select('COUNT(DISTINCT product.id) as total');

        $query->innerJoin(
            'product',
            's_articles_top_seller_ro',
            'pluginTopSeller',
            'pluginTopSeller.article_id = product.id'
        );

        $query->andWhere('pluginTopSeller.sales > 1');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);


        return $facet;
    }
}
```

## Define own terms
The term classes (sortings, facets and conditions) are defined with no dependency to the database platform of the ProductNumberSearch.

The Shopware\Bundle\SearchBundle provides interfaces for this classes:

- Shopware\Bundle\SearchBundle\ConditionInterface
- Shopware\Bundle\SearchBundle\SortingInterface
- Shopware\Bundle\SearchBundle\FacetInterface

### Sorting term
In most cases the sorting term contains a sorting direction for ascending or descending sorting.

The following sorting term defines that the product result has to be sorted by their sales ascending or descending.

**Notice: It is important to use the constant of the SortingInterface for the direction.**

```
<?php

namespace SwagSearchBundle\SearchBundle;

use Shopware\Bundle\SearchBundle\SortingInterface;

class PluginSorting implements SortingInterface
{
    private $direction;

    public function __construct($direction = SortingInterface::SORT_DESC)
    {
        $this->direction = $direction;
    }

    public function getName()
    {
        return 'plugin_sorting';
    }

    public function getDirection()
    {
        return $this->direction;
    }
}
```

### Condition term
In most cases the condition term contains some properties which containing the parameter for the query extension. For example the CategoryCondition class contains an array of category ids.

The following condition defines that only products with a provided min sales should be displayed. The handling is implemented in an associated platform  ConditionHandler.

```
<?php

namespace SwagSearchBundle\SearchBundle;

use Shopware\Bundle\SearchBundle\ConditionInterface;

class PluginCondition implements ConditionInterface
{
    private $minSales;

    public function __construct($minSales = 20)
    {
        $this->minSales = $minSales;
    }

    public function getName()
    {
        return 'plugin_condition';
    }

    public function getMinSales()
    {
        return $this->minSales;
    }
}
```

### Facet term
In most cases a facet term contains a property to store the total count of the products which matches to the facet definition. Additionally to the total property the facet contains in some cases a parameter property to shrink the query data.

The FacetInterface defines that a Facet has to implement a isFiltered function which returns true if the facet is already filtered by a condition.
For example, if the search result is filtered with a price range, the price facet is filtered.

The following facet selects the total count of products which sold more than X times.

```
<?php

namespace SwagSearchBundle\SearchBundle;

use Shopware\Bundle\SearchBundle\FacetInterface;

class PluginFacet implements FacetInterface
{
    private $total;

    private $minSales;

    private $filtered;

    public function getName()
    {
        return 'plugin_facet';
    }

    public function isFiltered()
    {
        return $this->filtered;
    }

    public function setFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getMinSales()
    {
        return $this->minSales;
    }

    public function setMinSales($minSales)
    {
        $this->minSales = $minSales;
    }
}
```
