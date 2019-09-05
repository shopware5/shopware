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

namespace Shopware\Bundle\SearchBundleES;

use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Shopware\Bundle\ESIndexingBundle\EsSearch;
use Shopware\Bundle\ESIndexingBundle\IndexFactoryInterface;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductNumberSearch implements ProductNumberSearchInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @var IndexFactoryInterface
     */
    private $indexFactory;

    /**
     * @var string
     */
    private $esVersion;

    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(
        Client $client,
        IndexFactoryInterface $indexFactory,
        $handlers,
        string $esVersion
    ) {
        $this->client = $client;
        $this->handlers = $handlers;
        $this->indexFactory = $indexFactory;
        $this->esVersion = $esVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Criteria $criteria, ShopContextInterface $context)
    {
        $search = $this->buildSearch($criteria, $context);
        $index = $this->indexFactory->createShopIndex($context->getShop(), ProductMapping::TYPE);

        $arguments = [
            'index' => $index->getName(),
            'type' => ProductMapping::TYPE,
            'body' => $search->toArray(),
        ];

        if (version_compare($this->esVersion, '7', '>=')) {
            $arguments = array_merge(
                $arguments,
                [
                    'rest_total_hits_as_int' => true,
                    'track_total_hits' => true,
                ]
            );
        }

        $data = $this->client->search(
            $arguments
        );

        $products = $this->createProducts($data);

        $result = new ProductNumberSearchResult(
            $products,
            $data['hits']['total'],
            []
        );

        if (isset($data['hits']['max_score'])) {
            $result->addAttribute('elastic_search', new Attribute(['max_score' => $data['hits']['max_score']]));
        }

        foreach ($this->handlers as $handler) {
            if (!($handler instanceof ResultHydratorInterface)) {
                continue;
            }
            $handler->hydrate($data, $result, $criteria, $context);
        }

        $facets = $this->sortFacets($criteria, $result);

        return new ProductNumberSearchResult(
            $products,
            $data['hits']['total'],
            $facets,
            $result->getAttributes()
        );
    }

    private function buildSearch(Criteria $criteria, ShopContextInterface $context): EsSearch
    {
        $search = new EsSearch();

        $this->addConditions($criteria, $context, $search);
        $this->addCriteriaParts($criteria, $context, $search, $criteria->getSortings());
        $this->addCriteriaParts($criteria, $context, $search, $criteria->getFacets());

        if ($criteria->getOffset() !== null) {
            $search->setFrom($criteria->getOffset());
        }
        if ($criteria->getLimit() !== null) {
            $search->setSize($criteria->getLimit());
        }
        $search->addSort(new FieldSort('id', 'asc'));

        return $search;
    }

    /**
     * @param CriteriaPartInterface[] $criteriaParts
     */
    private function addCriteriaParts(
        Criteria $criteria,
        ShopContextInterface $context,
        EsSearch $search,
        array $criteriaParts
    ) {
        foreach ($criteriaParts as $criteriaPart) {
            $handler = $this->getHandler($criteriaPart);

            $handler->handle($criteriaPart, $criteria, $search, $context);
        }
    }

    /**
     * @return HandlerInterface|PartialConditionHandlerInterface
     */
    private function getHandler(CriteriaPartInterface $criteriaPart)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($criteriaPart)) {
                return $handler;
            }
        }
        throw new \RuntimeException(sprintf('%s class not supported', get_class($criteriaPart)));
    }

    /**
     * @param array[] $data
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct[]
     */
    private function createProducts(array $data): array
    {
        $products = [];
        foreach ($data['hits']['hits'] as $row) {
            $source = $row['_source'];

            $product = new BaseProduct(
                (int) $source['id'],
                (int) $source['variantId'],
                $source['number']
            );

            $product->addAttribute(
                'elastic_search',
                new Attribute(array_merge(
                    $row['_source'],
                    ['score' => $row['_score']]
                ))
            );

            $products[$product->getNumber()] = $product;
        }

        return $products;
    }

    private function addConditions(
        Criteria $criteria,
        ShopContextInterface $context,
        EsSearch $search
    ): void {
        foreach ($criteria->getBaseConditions() as $condition) {
            $handler = $this->getHandler($condition);

            if ($handler instanceof PartialConditionHandlerInterface) {
                $handler->handleFilter($condition, $criteria, $search, $context);
            } else {
                trigger_error(sprintf("Condition handler %s doesn't support new filter mode. Class has to implement \\Shopware\\Bundle\\SearchBundleES\\PartialConditionHandlerInterface.", get_class($handler)), E_USER_DEPRECATED);
                $handler->handle($condition, $criteria, $search, $context);
            }
        }

        foreach ($criteria->getUserConditions() as $criteriaPart) {
            $handler = $this->getHandler($criteriaPart);

            // Trigger error when new interface isn't implemented
            if (!$handler instanceof PartialConditionHandlerInterface) {
                trigger_error(sprintf('Condition handler "%s" doesn\'t support new filter mode. Class has to implement "%s".', get_class($handler), PartialConditionHandlerInterface::class), E_USER_DEPRECATED);
            }

            // Filter mode active and handler doesn't supports the filter mode?
            if (!$handler instanceof PartialConditionHandlerInterface && $criteria->generatePartialFacets()) {
                throw new \Exception(sprintf('New filter mode activated, handler class %s doesn\'t support this mode', get_class($handler)));
            }

            // Filter mode active and handler supports new filter mode?
            if ($handler instanceof PartialConditionHandlerInterface && $criteria->generatePartialFacets()) {
                $handler->handleFilter($criteriaPart, $criteria, $search, $context);
                continue;
            }

            // Old filter mode activated and implements new interface?
            if ($handler instanceof PartialConditionHandlerInterface) {
                $handler->handlePostFilter($criteriaPart, $criteria, $search, $context);
            } else {
                $handler->handle($criteriaPart, $criteria, $search, $context);
            }
        }
    }

    private function sortFacets(Criteria $criteria, ProductNumberSearchResult $result): array
    {
        $sorting = array_map(function (FacetInterface $facet) {
            return $facet->getName();
        }, $criteria->getFacets());

        $sorting = array_flip(array_values($sorting));

        $sortedFacets = [];

        foreach ($result->getFacets() as $facetResult) {
            if (array_key_exists($facetResult->getFacetName(), $sorting)) {
                $position = $sorting[$facetResult->getFacetName()];
            } else {
                $position = count($sorting) + count($sortedFacets) + 1;
            }

            $sortedFacets[$position] = $facetResult;
        }

        ksort($sortedFacets, SORT_NUMERIC);

        return $sortedFacets;
    }
}
