<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;

class ProductNumberSearch implements SearchBundle\ProductNumberSearchInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var FacetHandlerInterface[]
     */
    private $facetHandlers;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Enlight_Event_EventManager $eventManager,
        IteratorAggregate $facetHandlers,
        Container $container
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->facetHandlers = iterator_to_array($facetHandlers, false);
        $this->eventManager = $eventManager;
        $this->facetHandlers = $this->registerFacetHandlers();

        $container->set('shopware_searchdbal.facet_handlers', $this->facetHandlers);
    }

    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @return SearchBundle\ProductNumberSearchResult
     */
    public function search(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->queryBuilderFactory->createProductQuery($criteria, $context);

        $products = $this->getProducts($query);

        $total = count($products);
        if ($criteria->fetchCount()) {
            $total = $this->getTotalCount($query);
        }

        $facets = $this->createFacets($criteria, $context);

        return new SearchBundle\ProductNumberSearchResult($products, (int) $total, $facets);
    }

    /**
     * @return array
     */
    private function getProducts(QueryBuilder $query)
    {
        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $products = [];

        foreach ($data as $row) {
            $product = new BaseProduct(
                (int) $row['__product_id'],
                (int) $row['__variant_id'],
                $row['__variant_ordernumber']
            );

            $product->addAttribute('search', new Attribute($row));
            $products[$product->getNumber()] = $product;
        }

        return $products;
    }

    /**
     * Calculated the total count of the whole search result.
     *
     * @param QueryBuilder $query
     *
     * @return int
     */
    private function getTotalCount($query)
    {
        return $query->getConnection()->fetchColumn('SELECT FOUND_ROWS()');
    }

    /**
     * @throws \Exception
     *
     * @return SearchBundle\FacetResultInterface[]
     */
    private function createFacets(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        if (count($criteria->getFacets()) === 0) {
            return [];
        }

        $facets = [];

        $clone = clone $criteria;

        if (!$criteria->generatePartialFacets()) {
            $clone->resetConditions();
            $clone->resetSorting();
        }

        foreach ($criteria->getFacets() as $facet) {
            $handler = $this->getFacetHandler($facet);

            if ($criteria->generatePartialFacets() && !$handler instanceof PartialFacetHandlerInterface) {
                throw new \RuntimeException(sprintf("New filter mode activated, handler class %s doesn't support this mode", get_class($handler)));
            }

            if ($handler instanceof PartialFacetHandlerInterface) {
                $result = $handler->generatePartialFacet($facet, $clone, $criteria, $context);
            } else {
                trigger_error(sprintf("Facet handler %s doesn't support new filter mode. FacetHandlerInterface is deprecated since version 5.3 and will be removed in 6.0.", get_class($handler)), E_USER_DEPRECATED);
                $result = $handler->generateFacet($facet, $criteria, $context);
            }

            if (!$result) {
                continue;
            }

            if (!is_array($result)) {
                $result = [$result];
            }

            $facets = array_merge($facets, $result);
        }

        return $facets;
    }

    /**
     * @return FacetHandlerInterface[]
     */
    private function registerFacetHandlers()
    {
        $facetHandlers = new ArrayCollection();
        $facetHandlers = $this->eventManager->collect(
            'Shopware_SearchBundleDBAL_Collect_Facet_Handlers',
            $facetHandlers
        );

        $this->assertCollectionIsInstanceOf(
            $facetHandlers,
            [
                __NAMESPACE__ . '\FacetHandlerInterface',
                __NAMESPACE__ . '\PartialFacetHandlerInterface',
            ]
        );

        return array_merge($facetHandlers->toArray(), $this->facetHandlers);
    }

    /**
     * @throws \Exception
     *
     * @return FacetHandlerInterface
     */
    private function getFacetHandler(SearchBundle\FacetInterface $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf('Facet %s not supported', get_class($facet)));
    }

    /**
     * @param string[] $classes
     */
    private function assertCollectionIsInstanceOf(ArrayCollection $objects, $classes)
    {
        foreach ($objects as $object) {
            $implements = false;
            foreach ($classes as $class) {
                if ($object instanceof $class) {
                    $implements = true;
                    break;
                }
            }
            if (!$implements) {
                throw new \RuntimeException(
                    sprintf(
                        'Object of class "%s" has to implement one of the following interfaces: "%s".',
                        get_class($object),
                        implode(',', $classes)
                    )
                );
            }
        }
    }
}
