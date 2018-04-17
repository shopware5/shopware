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

class BenchmarkCollector implements BenchmarkCollectorInterface
{
    /**
     * @var BenchmarkProviderInterface[]
     */
    private $providers;

    /**
     * @param \IteratorAggregate $providers
     */
    public function __construct(\IteratorAggregate $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $providerData = [];

        foreach ($this->providers as $provider) {
            $providerData[$provider->getName()] = $provider->getBenchmarkData();
        }

        $providerData = $this->moveShopData($providerData);

        return json_encode($providerData, true);
    }

    /**
     * Moves the array element 'shop' to the parent array and deletes the 'shop' element.
     *
     * @param array $providerData
     *
     * @throws \Exception
     *
     * @return array
     */
    private function moveShopData(array $providerData)
    {
        if (!array_key_exists('shop', $providerData)) {
            throw new \Exception('Necessary data with name \'shop\' not provided.');
        }

        $providerData = $providerData['shop'] + $providerData;
        unset($providerData['shop']);

        return $providerData;
    }
}
