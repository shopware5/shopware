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
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductNumberSearch implements SearchBundle\ProductNumberSearchInterface
{
    /**
     * @var \Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface
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

    /**
     * @param QueryBuilderFactoryInterface $queryBuilderFactory
     * @param \Enlight_Event_EventManager $eventManager
     * @param FacetHandlerInterface[] $facetHandlers
     */
    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Enlight_Event_EventManager $eventManager,
        $facetHandlers = []
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->facetHandlers = $facetHandlers;
        $this->eventManager = $eventManager;
        $this->facetHandlers = $this->registerFacetHandlers();
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
     * @param ShopContextInterface $context
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
     * @param QueryBuilder $query
     * @return array
     */
    private function getProducts(QueryBuilder $query)
    {
        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
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
     * @return int
     */
    private function getTotalCount($query)
    {
        return $query->getConnection()->fetchColumn('SELECT FOUND_ROWS()');
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param ShopContextInterface $context
     * @return SearchBundle\FacetResultInterface[]
     * @throws \Exception
     */
    private function createFacets(SearchBundle\Criteria $criteria, ShopContextInterface $context)
    {
        $facets = [];

        foreach ($criteria->getFacets() as $facet) {
            $handler = $this->getFacetHandler($facet);

            $result = $handler->generateFacet($facet, $criteria, $context);

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

        $this->assertCollectionIsInstanceOf($facetHandlers, __NAMESPACE__ . '\FacetHandlerInterface');

        return array_merge($facetHandlers->toArray(), $this->facetHandlers);
    }

    /**
     * @param SearchBundle\FacetInterface $facet
     * @throws \Exception
     * @return FacetHandlerInterface
     */
    private function getFacetHandler(SearchBundle\FacetInterface $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
    }

    /**
     * @param ArrayCollection $objects
     * @param string $class
     */
    private function assertCollectionIsInstanceOf(ArrayCollection $objects, $class)
    {
        foreach ($objects as $object) {
            if (!$object instanceof $class) {
                throw new \RuntimeException(
                    sprintf(
                        'Object of class "%s" must be instance of "%s".',
                        get_class($object),
                        $class
                    )
                );
            }
        }
    }
}
