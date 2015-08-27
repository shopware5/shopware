<?php

use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\PluginInstallerBundle\Service\AccountManagerService;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService;

class Shopware_Controllers_Backend_FirstRunWizardPluginManager extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Checks if shop has licenses configured.
     */
    public function checkShopLicenceAction()
    {
        $licenseCount = $this->container->get('dbal_connection')
            ->executeQuery('SELECT COUNT(DISTINCT id) FROM s_core_licenses')
            ->fetchColumn();

        $this->View()->assign(array(
            'success' => false,
            'data' => (bool) $licenseCount,
        ));
    }

    /**
     * Loads integrated plugins from SBP
     */
    public function getIntegratedPluginsAction()
    {
        /** @var PluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getIntegratedPlugins(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($plugins),
        ));
    }

    /**
     * Loads recommended plugins from SBP
     */
    public function getRecommendedPluginsAction()
    {
        /** @var PluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getRecommendedPlugins(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }


        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($plugins),
        ));
    }

    /**
     * Loads demo data plugins from SBP
     */
    public function getDemoDataPluginsAction()
    {
        /** @var PluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getDemoDataPlugins(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($plugins),
        ));
    }

    /**
     * Loads localization plugins from SBP
     */
    public function getLocalizationPluginsAction()
    {
        $localization = $this->Request()->get('localeId');

        /** @var PluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var PluginStruct[] $plugins */
            $plugins = $firstRunWizardPluginStore->getLocalizationPlugins(
                $localization,
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($plugins),
        ));
    }

    /**
     * Loads localizations list from SBP
     */
    public function getLocalizationsAction()
    {
        /** @var PluginStoreService $firstRunWizardPluginStore */
        $firstRunWizardPluginStore = $this->container->get('first_run_wizard_plugin_store');

        try {
            /** @var LocaleStruct[] $localizations */
            $localizations = $firstRunWizardPluginStore->getLocalizations(
                $this->getCurrentLocale(),
                $this->getVersion()
            );
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $localizations
        ));
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
     * @return LocaleStruct Information about the current locale
     * @throws Exception
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

        $user = Shopware()->Auth()->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;
        $localeCode = $locale->getLocale();

        return array_key_exists($localeCode, $locales) ? $locales[$localeCode] : null;
    }
}
