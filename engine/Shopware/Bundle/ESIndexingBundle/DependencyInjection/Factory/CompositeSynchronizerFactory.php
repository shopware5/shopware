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
use Shopware\Bundle\ESIndexingBundle\CompositeSynchronizer;
use Shopware\Bundle\ESIndexingBundle\SynchronizerInterface;
use Symfony\Component\DependencyInjection\Container;

class CompositeSynchronizerFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var SynchronizerInterface[]
     */
    private $synchronizer;

    /**
     * @param SynchronizerInterface[] $synchronizer
     */
    public function __construct($synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * @param Container $container
     * @return CompositeSynchronizer
     */
    public function factory(Container $container)
    {
        $this->container = $container;
        $synchronizer = $this->collectSynchronizer();
        return new CompositeSynchronizer($synchronizer);
    }

    /**
     * @return SynchronizerInterface[]
     */
    private function collectSynchronizer()
    {
        $collection = new ArrayCollection();
        $this->container->get('events')->collect(
            'Shopware_ESIndexingBundle_Collect_Synchronizer',
            $collection
        );
        return array_merge($collection->toArray(), $this->synchronizer);
    }
}
