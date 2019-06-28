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

namespace Shopware\Bundle\MediaBundle;

use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Shopware\Bundle\MediaBundle\Adapters\AdapterFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaServiceFactory
{
    /**
     * @var array
     */
    private $cdnConfig;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AdapterFactoryInterface[]
     */
    private $adapterFactories;

    public function __construct(ContainerInterface $container, IteratorAggregate $adapterFactories, array $cdnConfig)
    {
        $this->container = $container;
        $this->adapterFactories = iterator_to_array($adapterFactories, false);
        $this->cdnConfig = $cdnConfig;
    }

    /**
     * Return a new MediaService instance based on the configured storage type
     *
     * @param string $backendName
     *
     * @throws \Exception
     *
     * @return MediaServiceInterface
     */
    public function factory($backendName)
    {
        if (!isset($this->cdnConfig['adapters'][$backendName])) {
            throw new \Exception(sprintf('Configuration "%s" not found', $backendName));
        }

        // Filesystem
        $config = $this->cdnConfig['adapters'][$backendName];
        $adapter = $this->getAdapter($config);
        $filesystem = new Filesystem($adapter, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        // Strategy
        $strategyFactory = $this->container->get('shopware_media.strategy_factory');
        $strategyName = isset($config['strategy']) ? $config['strategy'] : $this->cdnConfig['strategy'];
        $strategy = $strategyFactory->factory($strategyName);

        return new MediaService($filesystem, $strategy, $this->container, $config);
    }

    /**
     * Collects third party adapters
     *
     * @param array $config
     *
     * @throws \Enlight_Event_Exception
     * @throws \Exception
     *
     * @return AdapterInterface
     */
    private function getAdapterByCollectEvent($config)
    {
        $adapters = new ArrayCollection();
        $adapters = $this->container->get('events')->collect('Shopware_Collect_MediaAdapter_' . $config['type'], $adapters, ['config' => $config]);

        $adapter = $adapters->first();

        if (!$adapter) {
            throw new \Exception(sprintf('CDN Adapter "%s" not found.', $config['type']));
        }

        return $adapter;
    }

    /**
     * @return AdapterInterface
     */
    private function getAdapter(array $config)
    {
        foreach ($this->adapterFactories as $factory) {
            if ($factory->getType() === $config['type']) {
                return $factory->create($config);
            }
        }

        return $this->getAdapterByCollectEvent($config);
    }
}
