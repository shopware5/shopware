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

use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

class FirstRunWizardPluginStoreService
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    /**
     * @var PluginLocalService
     */
    private $localPluginService;

    public function __construct(
        StructHydrator $hydrator,
        PluginLocalService $localPluginService,
        StoreClient $storeClient
    ) {
        $this->hydrator = $hydrator;
        $this->localPluginService = $localPluginService;
        $this->storeClient = $storeClient;
    }

    /**
     * Loads recommended plugins from SBP
     *
     * @param LocaleStruct|null $locale          Locale in which to translate the information
     * @param string            $shopwareVersion Current Shopware version
     *
     * @return array List of plugins
     */
    public function getRecommendedPlugins(LocaleStruct $locale = null, $shopwareVersion)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/recommendations',
            ['locale' => $localeName, 'shopwareVersion' => $shopwareVersion]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data);

        return $this->getAdditionallyLocalData($plugins);
    }

    /**
     * Loads integrated plugins from SBP
     *
     * @param string $isoCode         Two letter iso code indicating for which country to get the plugin list
     * @param string $shopwareVersion Current Shopware version
     *
     * @return array List of plugins
     */
    public function getIntegratedPlugins($isoCode, $shopwareVersion)
    {
        if (preg_match('/^([a-zA-Z]){2}$/', $isoCode) !== 1) {
            throw new \RuntimeException('Iso parameter format not allowed');
        }

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/countries/' . strtolower($isoCode),
            ['shopwareVersion' => $shopwareVersion]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data);

        return $this->getAdditionallyLocalData($plugins);
    }

    /**
     * Loads demo data plugins from SBP
     *
     * @param LocaleStruct|null $locale          Locale in which to translate the information
     * @param string            $shopwareVersion Current Shopware version
     *
     * @return array List of plugins
     */
    public function getDemoDataPlugins(LocaleStruct $locale = null, $shopwareVersion)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/demodata',
            ['locale' => $localeName, 'shopwareVersion' => $shopwareVersion]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data);

        return $this->getAdditionallyLocalData($plugins);
    }

    /**
     * Loads localization options from SBP
     *
     * @param LocaleStruct|null $locale          Locale in which to translate the information
     * @param string            $shopwareVersion Current Shopware version
     *
     * @return array List of plugins
     */
    public function getLocalizations(LocaleStruct $locale = null, $shopwareVersion)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/languages',
            ['locale' => $localeName, 'shopwareVersion' => $shopwareVersion]
        );

        return $data;
    }

    /**
     * Loads countries for integrated plugins from SBP
     *
     * @param LocaleStruct|null $locale Locale in which to translate the information
     *
     * @return string[] List of countries
     */
    public function getIntegratedPluginsCountries(LocaleStruct $locale = null)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/countries',
            ['locale' => $localeName]
        );

        return $data;
    }

    /**
     * Loads localization plugins from SBP for the given localization
     *
     * @param string            $localization    Localization for which to retrieve the plugins
     * @param LocaleStruct|null $locale          Locale in which to translate the information
     * @param string            $shopwareVersion Current Shopware version
     *
     * @return PluginStruct[] List of plugins
     */
    public function getLocalizationPlugins($localization, LocaleStruct $locale = null, $shopwareVersion)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/languages/' . $localization,
            ['locale' => $localeName, 'shopwareVersion' => $shopwareVersion]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data);

        return $this->getAdditionallyLocalData($plugins);
    }

    /**
     * Loads all available localization plugins from SBP
     *
     * @param LocaleStruct|null $locale          Locale in which to translate the information
     * @param string            $shopwareVersion Current Shopware version
     *
     * @return PluginStruct[] List of plugins
     */
    public function getAvailableLocalizations(LocaleStruct $locale = null, $shopwareVersion)
    {
        $localeName = $locale ? $locale->getName() : null;

        $data = $this->storeClient->doGetRequest(
            '/firstrunwizard/localizations',
            ['locale' => $localeName, 'shopwareVersion' => $shopwareVersion]
        );

        $plugins = $this->hydrator->hydrateStorePlugins($data);

        return $this->getAdditionallyLocalData($plugins);
    }

    /**
     * @param PluginStruct[] $plugins
     *
     * @return PluginStruct[]
     */
    private function getAdditionallyLocalData(array $plugins)
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
