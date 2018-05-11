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

namespace Shopware\Bundle\BenchmarkBundle;

class BenchmarkLocalCollector implements BenchmarkCollectorInterface
{
    /**
     * @var \IteratorAggregate
     */
    private $providers;

    /**
     * @var \IteratorAggregate
     */
    private $localProviders;

    /**
     * @var BenchmarkLocalHydrator
     */
    private $benchmarkLocalHydrator;

    /**
     * @param \IteratorAggregate     $providers
     * @param \IteratorAggregate     $localProviders
     * @param BenchmarkLocalHydrator $benchmarkLocalHydrator
     */
    public function __construct(\IteratorAggregate $providers, \IteratorAggregate $localProviders, BenchmarkLocalHydrator $benchmarkLocalHydrator)
    {
        $this->providers = $providers;
        $this->localProviders = $localProviders;
        $this->benchmarkLocalHydrator = $benchmarkLocalHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $providerData = [];

        /** @var BenchmarkProviderInterface $provider */
        foreach ($this->providers as $provider) {
            $providerData[$provider->getName()] = $provider->getBenchmarkData();
        }

        /** @var BenchmarkProviderInterface $localProvider */
        foreach ($this->localProviders as $localProvider) {
            $providerData[$localProvider->getName()] = $localProvider->getBenchmarkData();
        }

        return $this->benchmarkLocalHydrator->hydrate($providerData);
    }
}
