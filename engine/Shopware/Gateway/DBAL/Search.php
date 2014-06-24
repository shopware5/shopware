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

namespace Shopware\Gateway\DBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL as Gateway;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Result;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;
use Shopware\Gateway\Search\Product as SearchProduct;

/**
 * @package Shopware\Gateway\DBAL
 */
class Search implements \Shopware\Gateway\Search
{
    /**
     * @var Gateway\ConditionHandler\DBAL[]
     */
    private $conditionHandlers;

    /**
     * @var Gateway\FacetHandler\DBAL[]
     */
    private $facetHandlers;

    /**
     * @var Gateway\Hydrator\Attribute
     */
    private $attributeHydrator;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Gateway\ConditionHandler\Core
     */
    private $coreHandler;

    /**
     * @var Gateway\FacetHandler\Category
     */
    private $categoryHandler;

    /**
     * @var Gateway\FacetHandler\Manufacturer
     */
    private $manufacturerHandler;

    /**
     * @var Gateway\FacetHandler\Price
     */
    private $priceHandler;

    /**
     * @var Gateway\FacetHandler\Property
     */
    private $propertyHandler;

    /**
     * @var Gateway\FacetHandler\ShippingFree
     */
    private $shippingFreeHandler;

    /**
     * @var FacetHandler\ImmediateDelivery
     */
    private $immediateDeliveryHandler;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Attribute $attributeHydrator
     * @param \Enlight_Event_EventManager $eventManager
     * @param ConditionHandler\Core $coreHandler
     * @param FacetHandler\Manufacturer $manufacturerHandler
     * @param FacetHandler\Category $categoryHandler
     * @param FacetHandler\Price $priceHandler
     * @param FacetHandler\Property $propertyHandler
     * @param FacetHandler\ShippingFree $shippingFreeHandler
     * @param FacetHandler\ImmediateDelivery $immediateDeliveryHandler
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Attribute $attributeHydrator,
        \Enlight_Event_EventManager $eventManager,
        Gateway\ConditionHandler\Core $coreHandler,
        Gateway\FacetHandler\Manufacturer $manufacturerHandler,
        Gateway\FacetHandler\Category $categoryHandler,
        Gateway\FacetHandler\Price $priceHandler,
        Gateway\FacetHandler\Property $propertyHandler,
        Gateway\FacetHandler\ShippingFree $shippingFreeHandler,
        Gateway\FacetHandler\ImmediateDelivery $immediateDeliveryHandler
    ) {
        $this->entityManager = $entityManager;
        $this->attributeHydrator = $attributeHydrator;
        $this->eventManager = $eventManager;
        $this->coreHandler = $coreHandler;
        $this->categoryHandler = $categoryHandler;
        $this->manufacturerHandler = $manufacturerHandler;
        $this->priceHandler = $priceHandler;
        $this->propertyHandler = $propertyHandler;
        $this->shippingFreeHandler = $shippingFreeHandler;
        $this->immediateDeliveryHandler = $immediateDeliveryHandler;
    }

    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return \Shopware\Gateway\Search\Result
     */
    public function search(Criteria $criteria, Context $context)
    {
        $this->conditionHandlers = $this->registerConditionHandlers();

        $this->facetHandlers = $this->registerFacetHandlers();

        $products = $this->getProducts($criteria, $context);

        $total = $this->getTotalCount($criteria, $context);

        $facets = $this->createFacets($criteria, $context);

        $result = new Result(
            $products,
            intval($total),
            $facets
        );

        return $result;
    }

    /**
     * @return Gateway\ConditionHandler\DBAL[]
     */
    private function registerConditionHandlers()
    {
        $conditionHandlers = new ArrayCollection();
        $conditionHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Condition_Handlers',
            $conditionHandlers
        );

        $conditionHandlers[] = $this->coreHandler;
        return $conditionHandlers;
    }

    /**
     * @return Gateway\FacetHandler\DBAL[]
     */
    private function registerFacetHandlers()
    {
        $facetHandlers = new ArrayCollection();
        $facetHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Facet_Handlers',
            $facetHandlers
        );

        $facetHandlers[] = $this->manufacturerHandler;
        $facetHandlers[] = $this->propertyHandler;
        $facetHandlers[] = $this->priceHandler;
        $facetHandlers[] = $this->categoryHandler;
        $facetHandlers[] = $this->shippingFreeHandler;
        $facetHandlers[] = $this->immediateDeliveryHandler;

        return $facetHandlers;
    }

    /**
     * Calculated the total count of the whole search result.
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return mixed
     */
    private function getTotalCount(Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context);

        $query->resetQueryPart('groupBy')
            ->resetQueryPart('orderBy');

        $query->select('COUNT(DISTINCT products.id) as count');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Executes the base query to select the products.
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return array
     */
    private function getProducts(Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context)
            ->addSelect(array('variants.articleID', 'variants.ordernumber'))
            ->addGroupBy('products.id')
            ->setFirstResult($criteria->offset)
            ->setMaxResults($criteria->limit);

        $this->addSorting($criteria, $query, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();

        foreach ($data as $row) {
            $product = new SearchProduct();
            $product->setNumber($row['ordernumber']);

            unset($row['ordernumber']);

            if (!empty($row)) {
                $product->addAttribute(
                    'search',
                    $this->attributeHydrator->hydrate($row)
                );
            }
            $products[$product->getNumber()] = $product;
        }
        return $products;
    }

    /**
     * @param Criteria $criteria
     * @param Context $context
     * @return QueryBuilder
     */
    private function getQuery(Criteria $criteria, Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_articles', 'products')
            ->innerJoin(
                'products',
                's_articles_details',
                'variants',
                'variants.id = products.main_detail_id
                 AND variants.active = 1
                 AND products.active = 1'
            )
            ->innerJoin(
                'products',
                's_core_tax',
                'tax',
                'tax.id = products.taxID'
            );

        $this->addConditions($criteria, $query, $context);

        return $query;
    }

    /**
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet[]
     * @throws \Exception
     */
    private function createFacets(Criteria $criteria, Context $context)
    {
        $facets = array();

        foreach ($criteria->facets as $facet) {
            $query = $this->getQuery($criteria, $context);

            $handler = $this->getFacetHandler($facet);

            if ($handler === null) {
                throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
            }

            $facets[] = $handler->generateFacet($facet, $query, $criteria, $context);
        }

        return $facets;
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     *
     * @throws \Exception
     */
    private function addConditions(Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->conditions as $condition) {
            $generator = $this->getConditionHandler($condition);

            if ($generator === null) {
                throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
            }

            $generator->generateCondition($condition, $query, $context);
        }
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     * @throws \Exception
     */
    private function addSorting(Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->sortings as $sorting) {

            $generator = $this->getSortingGenerator($sorting);

            if ($generator === null) {
                throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
            }

            $generator->generateSorting($sorting, $query, $context);
        }
    }

    /**
     * @param Sorting $sorting
     * @return null|ConditionHandler\DBAL
     */
    private function getSortingGenerator(Sorting $sorting)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsSorting($sorting)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @param Facet $facet
     * @return null|FacetHandler\DBAL
     */
    private function getFacetHandler(Facet $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }
        return null;
    }

    /**
     * @param Condition $condition
     * @return null|ConditionHandler\DBAL
     */
    private function getConditionHandler(Condition $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        return null;
    }
}
