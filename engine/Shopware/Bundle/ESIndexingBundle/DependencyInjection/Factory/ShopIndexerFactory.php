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

namespace Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\ESIndexingBundle\DataIndexerInterface;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\ESIndexingBundle\SettingsInterface;
use Shopware\Bundle\ESIndexingBundle\ShopIndexer;
use Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ShopIndexerFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DataIndexerInterface[]
     */
    private $indexer;

    /**
     * @var MappingInterface[]
     */
    private $mappings;

    /**
     * @var SettingsInterface[]
     */
    private $settings;

    /**
     * @var string
     */
    private $esVersion;

    public function __construct(
        \Traversable $indexer,
        \Traversable $mappings,
        \Traversable $settings,
        string $esVersion
    ) {
        $this->indexer = iterator_to_array($indexer, false);
        $this->mappings = iterator_to_array($mappings, false);
        $this->settings = iterator_to_array($settings, false);
        $this->esVersion = $esVersion;
    }

    /**
     * @throws \Exception
     *
     * @return ShopIndexerInterface
     */
    public function factory(ContainerInterface $container)
    {
        $this->container = $container;

        $indexer = $this->collectIndexer();
        $mappings = $this->collectMappings();
        $settings = $this->collectSettings();
        /** @var \Elasticsearch\Client $client */
        $client = $this->container->get('shopware_elastic_search.client');
        /** @var \Shopware\Bundle\ESIndexingBundle\BacklogReaderInterface $backlogReader */
        $backlogReader = $this->container->get('shopware_elastic_search.backlog_reader');
        /** @var \Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface $backlogProcessor */
        $backlogProcessor = $this->container->get('shopware_elastic_search.backlog_processor');
        /** @var \Shopware\Bundle\ESIndexingBundle\IndexFactoryInterface $indexFactory */
        $indexFactory = $this->container->get('shopware_elastic_search.index_factory');
        /** @var \Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface $consoleHelper */
        $consoleHelper = $this->container->get('shopware_elastic_search.console.console_evaluation_helper');

        return new ShopIndexer(
            $client,
            $backlogReader,
            $backlogProcessor,
            $indexFactory,
            $consoleHelper,
            $indexer,
            $mappings,
            $settings,
            $this->esVersion
        );
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return DataIndexerInterface[]
     */
    private function collectIndexer()
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Indexer',
            $collection
        );

        return array_merge($collection->toArray(), $this->indexer);
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return MappingInterface[]
     */
    private function collectMappings()
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Mapping',
            $collection
        );

        return array_merge($collection->toArray(), $this->mappings);
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return SettingsInterface[]
     */
    private function collectSettings()
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Settings',
            $collection
        );

        return array_merge($collection->toArray(), $this->settings);
    }
}
