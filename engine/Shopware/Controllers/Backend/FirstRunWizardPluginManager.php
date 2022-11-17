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

use Shopware\Bundle\PluginInstallerBundle\Service\FirstRunWizardPluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use SwagPaymentPayPalUnified\Setup\FirstRunWizardInstaller;

class Shopware_Controllers_Backend_FirstRunWizardPluginManager extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Loads integrated plugins from SBP
     */
    public function getIntegratedPluginsAction()
    {
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        $isoFromRequest = $this->Request()->get('iso');

        $isoCode = $isoFromRequest ? $isoFromRequest : $this->getCurrentLocale()->getName();

        $isoCode = substr($isoCode, -2);

        try {
            $plugins = $firstRunWizardPluginStore->getIntegratedPlugins($isoCode, $this->getVersion());
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
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $plugins = $firstRunWizardPluginStore->getRecommendedPlugins($this->getCurrentLocale(), $this->getVersion());
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
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $plugins = $firstRunWizardPluginStore->getDemoDataPlugins($this->getCurrentLocale(), $this->getVersion());
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

        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $plugins = $firstRunWizardPluginStore->getLocalizationPlugins($localization, $this->getCurrentLocale(), $this->getVersion());
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
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $localizations = $firstRunWizardPluginStore->getLocalizations($this->getCurrentLocale(), $this->getVersion());
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

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
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $countries = $firstRunWizardPluginStore->getIntegratedPluginsCountries($this->getCurrentLocale());
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
     * Loads localizations list from SBP
     */
    public function getAvailableLocalizationsAction()
    {
        $firstRunWizardPluginStore = $this->container->get(FirstRunWizardPluginStoreService::class);

        try {
            $localizations = $firstRunWizardPluginStore->getAvailableLocalizations($this->getCurrentLocale(), $this->getVersion());
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $storeClient = $this->container->get(StoreClient::class);
        $storeClient->doTrackEvent('First Run Wizard started');

        $this->View()->assign([
            'success' => true,
            'data' => array_values($localizations),
        ]);
    }

    public function saveConfigurationAction(): void
    {
        $clientId = $this->Request()->getParam('clientId');
        $clientSecret = $this->Request()->getParam('clientSecret');
        $sandbox = (bool) $this->Request()->getParam('sandbox');
        $payPalPlusEnabled = (bool) $this->Request()->getParam('payPalPlus');

        if (!class_exists('\SwagPaymentPayPalUnified\Setup\FirstRunWizardInstaller')) {
            throw new Exception(sprintf('Class %s does not exist.', '\SwagPaymentPayPalUnified\Setup\FirstRunWizardInstaller'));
        }

        $payPalInstaller = new FirstRunWizardInstaller();
        $payPalInstaller->saveConfiguration($this->get('dbal_connection'), [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'sandbox' => $sandbox,
            'payPalPlusEnabled' => $payPalPlusEnabled,
        ]);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    private function getVersion(): string
    {
        $version = $this->container->getParameter('shopware.release.version');

        if (!\is_string($version)) {
            throw new RuntimeException('Parameter shopware.release.version has to be an string');
        }

        return $version;
    }

    /**
     * Fetches known server locales. Returns a struct in server format containing
     * info about the current user's locale.
     *
     * @return LocaleStruct|null Information about the current locale
     */
    private function getCurrentLocale(): ?LocaleStruct
    {
        static $locales;

        if (\is_array($locales) && empty($locales)) {
            $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

            foreach ($accountManagerService->getLocales() as $serverLocale) {
                $locales[$serverLocale->getName()] = $serverLocale;
            }
        }

        $localeCode = Shopware()->Container()->get('auth')->getIdentity()->locale->getLocale();

        if (\array_key_exists($localeCode, $locales)) {
            return $locales[$localeCode];
        }

        // Fallback to english locale when available
        if (\array_key_exists('en_GB', $locales)) {
            return $locales['en_GB'];
        }

        return null;
    }
}
