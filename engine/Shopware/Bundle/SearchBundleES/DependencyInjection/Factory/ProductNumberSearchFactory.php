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

namespace Shopware\Bundle\SearchBundleES\DependencyInjection\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Elasticsearch\Client;
use IteratorAggregate;
use Shopware\Bundle\ESIndexingBundle\IndexFactory;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ProductNumberSearch;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductNumberSearchFactory
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @var string
     */
    private $esVersion;

    public function __construct(
        IteratorAggregate $handlers,
        string $esVersion
    ) {
        $this->handlers = iterator_to_array($handlers, false);
        $this->esVersion = $esVersion;
    }

    /**
     * @return ProductNumberSearch
     */
    public function factory(ContainerInterface $container)
    {
        /** @var Client $searchClient */
        $searchClient = $container->get('shopware_elastic_search.client');
        /** @var IndexFactory $indexFactory */
        $indexFactory = $container->get('shopware_elastic_search.index_factory');

        return new ProductNumberSearch(
            $searchClient,
            $indexFactory,
            $container->get('shopware_search_es.handler_collection')->toArray(),
            $this->esVersion
        );
    }

    /**
     * @return ArrayCollection
     *
     * @deprecated since Shopware 5.6, will be removed with 5.7. Please use the di tag shopware_search_es.search_handler instead
     */
    public function registerHandlerCollection(ContainerInterface $container)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6, will be removed in 5.7. Please use the di tag shopware_search_es.search_handler instead', __CLASS__, __FUNCTION__), E_USER_DEPRECATED);
        $handlers = $this->registerHandlers($container);

        return new ArrayCollection($handlers);
    }

    /**
     * @throws \Exception
     *
     * @return \Shopware\Bundle\SearchBundleES\HandlerInterface[]
     */
    private function registerHandlers(ContainerInterface $container)
    {
        $handlers = new ArrayCollection();
        $handlers = $container->get('events')->collect(
            'Shopware_SearchBundleES_Collect_Handlers',
            $handlers
        );

        return array_merge($handlers->toArray(), $this->handlers);
    }
}
