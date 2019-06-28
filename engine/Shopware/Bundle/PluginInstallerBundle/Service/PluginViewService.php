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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Shopware\Bundle\PluginInstallerBundle\Context\BaseRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;
use Shopware\Bundle\PluginInstallerBundle\Struct\UpdateResultStruct;

class PluginViewService
{
    /**
     * @var PluginLocalService
     */
    private $localPluginService;

    /**
     * @var PluginStoreService
     */
    private $storePluginService;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    public function __construct(
        PluginLocalService $localPluginService,
        PluginStoreService $storePluginService,
        StructHydrator $hydrator
    ) {
        $this->localPluginService = $localPluginService;
        $this->storePluginService = $storePluginService;
        $this->hydrator = $hydrator;
    }

    /**
     * @return PluginStruct
     */
    public function getPlugin(PluginsByTechnicalNameRequest $context)
    {
        $locale = $this->localPluginService->getPlugin($context);

        if (!$locale instanceof PluginStruct) {
            return $this->storePluginService->getPlugin($context);
        }

        $store = $this->storePluginService->getPlugin($context);

        if ($store) {
            $this->hydrator->assignStorePluginStruct($locale, $store);
        }

        return $locale;
    }

    /**
     * @return array
     */
    public function getPlugins(PluginsByTechnicalNameRequest $context)
    {
        $localePlugins = $this->localPluginService->getPlugins($context);

        $storePlugins = $this->storePluginService->getPlugins($context);

        $result = [];
        foreach ($context->getTechnicalNames() as $name) {
            $name = strtolower($name);
            $storePlugin = null;
            $localPlugin = null;

            if (array_key_exists($name, $localePlugins)) {
                $localPlugin = $localePlugins[$name];
            }

            if (array_key_exists($name, $storePlugins)) {
                $storePlugin = $storePlugins[$name];
            }

            switch (true) {
                case $localPlugin !== null && $storePlugin !== null:
                    $this->hydrator->assignStorePluginStruct($localPlugin, $storePlugin);
                    $result[$name] = $localPlugin;
                    break;

                case $localPlugin !== null:
                    $result[$name] = $localPlugin;
                    break;

                case $storePlugin !== null:
                    $result[$name] = $storePlugin;
                    break;
            }
        }

        return $result;
    }

    /**
     * @return ListingResultStruct
     */
    public function getStoreListing(ListingRequest $context)
    {
        $store = $this->storePluginService->getListing($context);

        $merged = $this->getAdditionallyLocalData($store->getPlugins());

        return new ListingResultStruct(
            $merged,
            $store->getTotalCount()
        );
    }

    /**
     * @return PluginStruct[]
     */
    public function getLocalListing(ListingRequest $context)
    {
        $local = $this->localPluginService->getListing($context);

        try {
            $merged = $this->getAdditionallyStoreData(
                $local->getPlugins(),
                $context
            );
        } catch (\Exception $e) {
            return $local->getPlugins();
        }

        return $merged;
    }

    /**
     * @return UpdateResultStruct
     */
    public function getUpdates(UpdateListingRequest $context)
    {
        /** @var UpdateResultStruct $result */
        $result = $this->storePluginService->getUpdates($context);
        $store = $result->getPlugins();

        $merged = $this->getAdditionallyLocalData($store);

        $plugins = [];
        foreach ($merged as $plugin) {
            if ($plugin->isUpdateAvailable()) {
                $plugins[] = $plugin;
            }
        }
        $result->setPlugins($plugins);

        return $result;
    }

    /**
     * @param PluginStruct[] $plugins
     *
     * @return PluginStruct[]
     */
    private function getAdditionallyStoreData($plugins, BaseRequest $context)
    {
        $names = array_keys($plugins);

        $storeContext = new PluginsByTechnicalNameRequest(
            $context->getLocale(),
            $context->getShopwareVersion(),
            $names
        );

        $store = $this->storePluginService->getPlugins($storeContext);

        $merged = [];
        foreach ($plugins as $plugin) {
            $key = strtolower($plugin->getTechnicalName());

            if (!array_key_exists($key, $store)) {
                $merged[$key] = $plugin;
                continue;
            }

            $storePlugin = $store[$key];

            $this->hydrator->assignStorePluginStruct(
                $plugin,
                $storePlugin
            );

            $merged[$key] = $plugin;
        }

        return $merged;
    }

    /**
     * @param PluginStruct[] $plugins
     *
     * @return PluginStruct[]
     */
    private function getAdditionallyLocalData($plugins)
    {
        $context = new PluginsByTechnicalNameRequest(
            null,
            null,
            array_keys($plugins)
        );

        $local = $this->localPluginService->getPlugins($context);

        $merged = [];

        foreach ($plugins as &$plugin) {
            $key = strtolower($plugin->getTechnicalName());

            if (!array_key_exists($key, $local)) {
                $merged[$key] = $plugin;
                continue;
            }

            $localPlugin = $local[$key];

            $this->hydrator->assignLocalPluginStruct(
                $plugin,
                $localPlugin
            );

            $merged[$key] = $plugin;
        }

        return $merged;
    }
}
