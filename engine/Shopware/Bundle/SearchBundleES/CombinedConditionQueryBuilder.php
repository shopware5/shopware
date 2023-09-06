<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use RuntimeException;
use Shopware\Bundle\ESIndexingBundle\EsSearch;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;

class CombinedConditionQueryBuilder
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return BoolQuery
     */
    public function build(array $conditions, Criteria $criteria, ShopContextInterface $context)
    {
        $search = new EsSearch();
        $handlerRegistry = $this->container->get(HandlerRegistry::class);

        if (!$handlerRegistry instanceof HandlerRegistry) {
            throw new RuntimeException(sprintf('%s is missing', HandlerRegistry::class));
        }

        foreach ($conditions as $condition) {
            $handler = $handlerRegistry->getHandler($condition);

            if ($handler instanceof PartialConditionHandlerInterface) {
                $handler->handleFilter($condition, $criteria, $search, $context);
            } else {
                $handler->handle($condition, $criteria, $search, $context);
            }
        }

        $query = new BoolQuery();

        if ($search->getPostFilters()) {
            $query->add($search->getPostFilters());
        }

        if ($search->getQueries()->getQueries(BoolQuery::FILTER)) {
            foreach ($search->getQueries()->getQueries(BoolQuery::FILTER) as $filter) {
                $query->add($filter, BoolQuery::FILTER);
            }
        }
        if ($search->getQueries()) {
            $query->add($search->getQueries());
        }

        return $query;
    }
}
