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

namespace Shopware\Bundle\SearchBundle\DBAL;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Doctrine\Common\Collections\ArrayCollection;

use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;

/**
 * @package Shopware\Bundle\SearchBundle\DBAL
 */
class ProductNumberSearch implements SearchBundle\ProductNumberSearchInterface
{
    /**
     * @var ConditionHandlerInterface[]
     */
    private $conditionHandlers;

    /**
     * @var FacetHandlerInterface[]
     */
    private $facetHandlers;

    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ConditionHandler\CoreConditionHandler
     */
    private $coreHandler;

    /**
     * @var FacetHandler\CategoryFacetHandler
     */
    private $categoryHandler;

    /**
     * @var FacetHandler\ManufacturerFacetHandler
     */
    private $manufacturerHandler;

    /**
     * @var FacetHandler\PriceFacetHandler
     */
    private $priceHandler;

    /**
     * @var FacetHandler\PropertyFacetHandler
     */
    private $propertyHandler;

    /**
     * @var FacetHandler\ShippingFreeFacetHandler
     */
    private $shippingFreeHandler;

    /**
     * @var FacetHandler\ImmediateDeliveryFacetHandler
     */
    private $immediateDeliveryHandler;

    /**
     * @param ModelManager $entityManager
     * @param AttributeHydrator $attributeHydrator
     * @param \Enlight_Event_EventManager $eventManager
     * @param ConditionHandler\CoreConditionHandler $coreHandler
     * @param FacetHandler\ManufacturerFacetHandler $manufacturerHandler
     * @param FacetHandler\CategoryFacetHandler $categoryHandler
     * @param FacetHandler\PriceFacetHandler $priceHandler
     * @param FacetHandler\PropertyFacetHandler $propertyHandler
     * @param FacetHandler\ShippingFreeFacetHandler $shippingFreeHandler
     * @param FacetHandler\ImmediateDeliveryFacetHandler $immediateDeliveryHandler
     */
    function __construct(
        ModelManager $entityManager,
        AttributeHydrator $attributeHydrator,
        \Enlight_Event_EventManager $eventManager,
        ConditionHandler\CoreConditionHandler $coreHandler,
        FacetHandler\ManufacturerFacetHandler $manufacturerHandler,
        FacetHandler\CategoryFacetHandler $categoryHandler,
        FacetHandler\PriceFacetHandler $priceHandler,
        FacetHandler\PropertyFacetHandler $propertyHandler,
        FacetHandler\ShippingFreeFacetHandler $shippingFreeHandler,
        FacetHandler\ImmediateDeliveryFacetHandler $immediateDeliveryHandler
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
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return SearchBundle\ProductNumberSearchResult
     */
    public function search(SearchBundle\Criteria $criteria, Context $context)
    {
        $this->conditionHandlers = $this->registerConditionHandlers();

        $this->facetHandlers = $this->registerFacetHandlers();

        $products = $this->getProducts($criteria, $context);

        $total = $this->getTotalCount($criteria, $context);

        $facets = $this->createFacets($criteria, $context);

        $result = new SearchBundle\ProductNumberSearchResult(
            $products,
            intval($total),
            $facets
        );

        return $result;
    }

    /**
     * @return ConditionHandlerInterface[]
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
     * @return FacetHandlerInterface[]
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
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return mixed
     */
    private function getTotalCount(SearchBundle\Criteria $criteria, Context $context)
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
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return array
     */
    private function getProducts(SearchBundle\Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context)
            ->addSelect(array('variants.articleID', 'variants.ordernumber'))
            ->addGroupBy('products.id')
            ->setFirstResult($criteria->getOffset())
            ->setMaxResults($criteria->getLimit());

        $this->addSorting($criteria, $query, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();

        foreach ($data as $row) {
            $product = new SearchBundle\SearchProduct();
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
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return QueryBuilder
     */
    private function getQuery(SearchBundle\Criteria $criteria, Context $context)
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
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return SearchBundle\FacetInterface[]
     * @throws \Exception
     */
    private function createFacets(SearchBundle\Criteria $criteria, Context $context)
    {
        $facets = array();

        foreach ($criteria->getFacets() as $facet) {
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
     * @param SearchBundle\Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     *
     * @throws \Exception
     */
    private function addConditions(SearchBundle\Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->getConditions() as $condition) {
            $generator = $this->getConditionHandler($condition);

            if ($generator === null) {
                throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
            }

            $generator->generateCondition($condition, $query, $context);
        }
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     * @throws \Exception
     */
    private function addSorting(SearchBundle\Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->getSortings() as $sorting) {

            $generator = $this->getSortingGenerator($sorting);

            if ($generator === null) {
                throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
            }

            $generator->generateSorting($sorting, $query, $context);
        }
    }

    /**
     * @param SearchBundle\SortingInterface $sorting
     * @return ConditionHandlerInterface
     */
    private function getSortingGenerator(SearchBundle\SortingInterface $sorting)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsSorting($sorting)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @param SearchBundle\FacetInterface $facet
     * @return FacetHandlerInterface
     */
    private function getFacetHandler(SearchBundle\FacetInterface $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }
        return null;
    }

    /**
     * @param SearchBundle\ConditionInterface $condition
     * @return null|ConditionHandlerInterface
     */
    private function getConditionHandler(SearchBundle\ConditionInterface $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        return null;
    }
}
