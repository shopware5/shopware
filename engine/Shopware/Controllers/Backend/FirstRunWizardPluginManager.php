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

use Shopware\Bundle\PluginInstallerBundle\Service\AccountManagerService;
use Shopware\Bundle\PluginInstallerBundle\Service\FirstRunWizardPluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;

class Shopware_Controllers_Backend_FirstRunWizardPluginManager extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Loads integrated plugins from SBP
     */
    public function getIntegratedPluginsAction()
    {
        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        $isoFromRequest = $this->Request()->get('iso');

        $isoCode = $isoFromRequest ? $isoFromRequest : $this->getCurrentLocale()->getName();

        $isoCode = substr($isoCode, -2);

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getIntegratedPlugins(
                $isoCode,
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
        ]);
    }

    /**
     * Loads recommended plugins from SBP
     */
    public function getRecommendedPluginsAction()
    {
        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getRecommendedPlugins(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
        ]);
    }

    /**
     * Loads demo data plugins from SBP
     */
    public function getDemoDataPluginsAction()
    {
        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getDemoDataPlugins(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
        ]);
    }

    /**
     * Loads localization plugins from SBP
     */
    public function getLocalizationPluginsAction()
    {
        $localization = $this->Request()->get('localeId');

        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getLocalizationPlugins(
                $localization,
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
        ]);
    }

    /**
     * Loads localizations list from SBP
     */
    public function getLocalizationsAction()
    {
        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var LocaleStruct[] $localizations */
            $localizations = $firstRunWizardPluginStore->getLocalizations(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        /** @var \Shopware\Bundle\PluginInstallerBundle\StoreClient $storeClient */
        $storeClient = $this->container->get('shopware_plugininstaller.store_client');
        $storeClient->doTrackEvent('First Run Wizard started');

        $this->View()->assign([
            'success' => true,
            'data' => $localizations,
        ]);
    }

    /**
     * Loads localizations list from SBP
     */
    public function getIntegratedPluginsCountriesAction()
    {
        /** @var FirstRunWizardPluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        $locale = $this->getCurrentLocale();

        try {
            $countries = $firstRunWizardPluginStore->getIntegratedPluginsCountries($locale);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $countries,
        ]);
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        return Shopware::VERSION;
    }

    /**
     * Fetches known server locales. Returns a struct in server format containing
     * info about the current user's locale.
     *
     * @throws Exception
     *
     * @return LocaleStruct Information about the current locale
     */
    private function getCurrentLocale()
    {
        static $locales;

        if (empty($locales)) {
            /** @var AccountManagerService $accountManagerService */
            $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

            /** @var LocaleStruct[] $serverLocales */
            $serverLocales = $accountManagerService->getLocales();

            foreach ($serverLocales as $serverLocale) {
                $locales[$serverLocale->getName()] = $serverLocale;
            }
        }

        $user = Shopware()->Container()->get('Auth')->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;
        $localeCode = $locale->getLocale();

        return array_key_exists($localeCode, $locales) ? $locales[$localeCode] : null;
    }
}
