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
use Shopware\Components\DependencyInjection\Container;

class ShopIndexerFactory
{
    /**
     * @var Container
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
     * @param DataIndexerInterface[] $indexer
     * @param MappingInterface[]     $mappings
     * @param SettingsInterface[]    $settings
     */
    public function __construct(array $indexer, array $mappings, array $settings)
    {
        $this->indexer = $indexer;
        $this->mappings = $mappings;
        $this->settings = $settings;
    }

    /**
     * @param Container $container
     *
     * @throws \Exception
     *
     * @return ShopIndexerInterface
     */
    public function factory(Container $container)
    {
        $this->container = $container;

        $indexer = $this->collectIndexer();
        $mappings = $this->collectMappings();
        $settings = $this->collectSettings();

        return new ShopIndexer(
            $this->container->get('shopware_elastic_search.client'),
            $this->container->get('shopware_elastic_search.backlog_reader'),
            $this->container->get('shopware_elastic_search.backlog_processor'),
            $this->container->get('shopware_elastic_search.index_factory'),
            $indexer,
            $mappings,
            $settings,
            $this->container->getParameter('shopware.es')
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
