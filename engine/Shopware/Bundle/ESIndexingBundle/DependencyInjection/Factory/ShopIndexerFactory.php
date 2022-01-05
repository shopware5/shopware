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
use Elasticsearch\Client;
use Enlight_Event_Exception;
use Exception;
use Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface;
use Shopware\Bundle\ESIndexingBundle\BacklogReader;
use Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface;
use Shopware\Bundle\ESIndexingBundle\DataIndexerInterface;
use Shopware\Bundle\ESIndexingBundle\IndexFactory;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Bundle\ESIndexingBundle\SettingsInterface;
use Shopware\Bundle\ESIndexingBundle\ShopIndexer;
use Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Traversable;

class ShopIndexerFactory
{
    private ContainerInterface $container;

    /**
     * @var DataIndexerInterface[]
     */
    private array $indexer;

    /**
     * @var MappingInterface[]
     */
    private array $mappings;

    /**
     * @var SettingsInterface[]
     */
    private array $settings;

    public function __construct(
        Traversable $indexer,
        Traversable $mappings,
        Traversable $settings
    ) {
        $this->indexer = iterator_to_array($indexer, false);
        $this->mappings = iterator_to_array($mappings, false);
        $this->settings = iterator_to_array($settings, false);
    }

    /**
     * @throws Exception
     *
     * @return ShopIndexerInterface
     */
    public function factory(ContainerInterface $container)
    {
        $this->container = $container;

        $indexer = $this->collectIndexer();
        $mappings = $this->collectMappings();
        $settings = $this->collectSettings();
        $client = $this->container->get(Client::class);
        $backlogReader = $this->container->get(BacklogReader::class);
        $backlogProcessor = $this->container->get(BacklogProcessorInterface::class);
        $indexFactory = $this->container->get(IndexFactory::class);
        $consoleHelper = $this->container->get(EvaluationHelperInterface::class);

        return new ShopIndexer(
            $client,
            $backlogReader,
            $backlogProcessor,
            $indexFactory,
            $consoleHelper,
            $indexer,
            $mappings,
            $settings
        );
    }

    /**
     * @throws Enlight_Event_Exception
     *
     * @return DataIndexerInterface[]
     */
    private function collectIndexer(): array
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Indexer',
            $collection
        );

        return array_merge($collection->toArray(), $this->indexer);
    }

    /**
     * @throws Enlight_Event_Exception
     *
     * @return MappingInterface[]
     */
    private function collectMappings(): array
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Mapping',
            $collection
        );

        return array_merge($collection->toArray(), $this->mappings);
    }

    /**
     * @throws Enlight_Event_Exception
     *
     * @return SettingsInterface[]
     */
    private function collectSettings(): array
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Settings',
            $collection
        );

        return array_merge($collection->toArray(), $this->settings);
    }
}
