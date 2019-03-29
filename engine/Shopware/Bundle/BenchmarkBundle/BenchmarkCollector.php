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

use Shopware\Bundle\BenchmarkBundle\Provider\UpdatedOrdersProvider;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BenchmarkCollector implements BenchmarkCollectorInterface
{
    /**
     * @var \IteratorAggregate
     */
    private $providers;

    public function __construct(\IteratorAggregate $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function get(ShopContextInterface $shopContext, $batchSize = null)
    {
        $providerData = [];

        /** @var BenchmarkProviderInterface $provider */
        foreach ($this->providers as $provider) {
            if ($provider instanceof BatchableProviderInterface) {
                // Updated Orders Provider may only be called if no initial order has to be transmitted
                if ($provider instanceof UpdatedOrdersProvider && $providerData['orders']['list']) {
                    $providerData[$provider->getName()]['list'] = [];
                    continue;
                }

                $providerData[$provider->getName()] = $provider->getBenchmarkData($shopContext, $batchSize);

                continue;
            }

            $providerData[$provider->getName()] = $provider->getBenchmarkData($shopContext);
        }

        $providerData = $this->moveUpdatedOrdersData($providerData);

        return $this->moveShopData($providerData);
    }

    /**
     * Moves the array element 'shop' to the parent array and deletes the 'shop' element.
     *
     * @throws \Exception
     *
     * @return array
     */
    private function moveShopData(array $providerData)
    {
        $shopDataArrayKey = 'shop';
        if (!array_key_exists($shopDataArrayKey, $providerData)) {
            throw new \Exception(sprintf('Necessary data with name \'%s\' not provided.', $shopDataArrayKey));
        }

        $providerData = $providerData[$shopDataArrayKey] + $providerData;
        unset($providerData[$shopDataArrayKey]);

        return $providerData;
    }

    /**
     * Moves the "updated_orders" array into the "orders" key and leaves a hint about that moving
     *
     * @return array
     */
    private function moveUpdatedOrdersData(array $providerData)
    {
        // Nothing to be moved
        if (!array_key_exists('updated_orders', $providerData) || !$providerData['updated_orders']['list']) {
            return $providerData;
        }

        $providerData['orders'] = $providerData['updated_orders'];

        // Necessary to know that the "orders" key contains the updated_orders later on
        // This is used in the StatisticsService to update the "last_updated_orders_date" column
        $providerData['updated_orders']['moved'] = true;
        unset($providerData['updated_orders']['list']);

        return $providerData;
    }
}
