<?php

use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\PluginInstallerBundle\Service\AccountManagerService;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;

class Shopware_Controllers_Backend_FirstRunWizard extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Saves the current wizard status (enabled/disabled) to the database
     */
    public function saveEnabledAction()
    {
        $value = (bool) $this->Request()->getParam('value');
        $element = Shopware()->Models()
            ->getRepository('Shopware\Models\Config\Element')
            ->findOneBy(array('name' => 'firstRunWizardEnabled'));

        $defaultShop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getDefault();

        $requestElements = array(
            array(
                'id' => $element->getId(),
                'name' => $element->getName(),
                'values' => array(
                    array(
                        'value' => $value,
                        'shopId' => $defaultShop->getId()
                    )
                ),
                'type' => 'number'
            )
        );

        /** @var \Shopware\Bundle\PluginInstallerBundle\StoreClient $storeClient */
        $storeClient = $this->container->get('shopware_plugininstaller.store_client');
        $storeClient->doTrackEvent('First Run Wizard completed');

        $this->Request()->setParam('elements', $requestElements);

        $this->forward('saveForm', 'Config');
    }

    /**
     * Saves the current wizard step to the database
     */
    public function saveConfigurationAction()
    {
        $values = $this->Request()->getParams();
        $defaultShop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getDefault();

        /**
         * Save theme config
         */
        $themeConfigKeys = [
            'desktopLogo'
        ];

        $themeConfigValues = array_map(function ($configKey) use ($defaultShop, $values) {
            return [
                'elementName' => $configKey,
                'shopId' => $defaultShop->getId(),
                'value' => array_key_exists($configKey, $values) ? $values[$configKey] : ''
            ];
        }, $themeConfigKeys);

        $theme = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Template')
            ->findOneBy(array('template' => 'Responsive'));

        $this->container->get('theme_service')->saveConfig(
            $theme,
            $themeConfigValues
        );

        /**
         * Save shop config
         */
        $shopConfigKeys = [
            'address',
            'bankAccount',
            'company',
            'metaIsFamilyFriendly'
        ];

        $shopConfigValues = array_intersect_key($values, array_flip($shopConfigKeys));
        $shopConfigValues['metaIsFamilyFriendly'] = (bool) ($shopConfigValues['metaIsFamilyFriendly'] === "true");

        $requestElements = array();

        foreach ($shopConfigValues as $configName => $configValue) {
            $element = Shopware()->Models()
                ->getRepository('Shopware\Models\Config\Element')
                ->findOneBy(array('name' => $configName));

            $requestElements[] = array(
                'id' => $element->getId(),
                'name' => $element->getName(),
                'values' => array(
                    array(
                        'value' => $configValue,
                        'shopId' => $defaultShop->getId()
                    )
                ),
                'type' => 'number'
            );
        }

        $this->Request()->setParam('elements', $requestElements);

        $this->forward('saveForm', 'Config');
    }

    /**
     * Saves the current wizard step to the database
     */
    public function loadConfigurationAction()
    {
        $defaultShop = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Shop')
            ->getDefault();
        $theme = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Template')
            ->findOneBy(array('template' => 'Responsive'));

        /**
         * Load theme config values
         */
        $themeConfigKeys = [
            'desktopLogo'
        ];

        $themeConfigData = $this->container->get('theme_service')->getConfig(
            $theme,
            $defaultShop,
            $themeConfigKeys
        );

        $themeConfigValues = [];

        foreach ($themeConfigData as $themeConfig) {
            if (empty($themeConfig['values'])) {
                $value = $themeConfig['defaultValue'];
            } else {
                $valueArray = array_shift($themeConfig['values']);
                $value = $valueArray['value'];
            }

            $themeConfigValues[$themeConfig['name']] = $value;
        }

        /**
         * Load shop config values
         */
        $shopConfigKeys = [
            'address',
            'bankAccount',
            'company',
            'metaIsFamilyFriendly'
        ];

        $builder = $this->container->get('models')->createQueryBuilder();
        $builder->select(array(
            'elements',
            'values'
        ))
            ->from('Shopware\Models\Config\Element', 'elements')
            ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->where('elements.name IN (:optionNames)')
            ->orderBy('elements.id')
            ->setParameter('shopId', $defaultShop->getId())
            ->setParameter('optionNames', $shopConfigKeys);

        $shopConfigData = $builder->getQuery()->getArrayResult();

        $shopConfigValues = [];

        foreach ($shopConfigData as $shopConfig) {
            if (empty($shopConfig['values'])) {
                $value = $shopConfig['value'];
            } else {
                $valueArray = array_shift($shopConfig['values']);
                $value = $valueArray['value'];
            }

            $shopConfigValues[$shopConfig['name']] = $value;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array_merge($shopConfigValues, $themeConfigValues)
        ));
    }

    /**
     * Tests connectivity to SBP server
     *
     * @return Enlight_View|Enlight_View_Default
     * @throws Exception
     */
    public function pingServerAction()
    {
        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $isConnected = $accountManagerService->pingServer();
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'message' => $isConnected
        ));
    }

    /**
     * Gets the available backend locales excluding the current one
     */
    public function getAlternativeLocalesAction()
    {
        /** @var $locale \Shopware\Models\Shop\Locale */
        $targetLocale = Shopware()->Container()->get('Auth')->getIdentity()->locale;

        /** @var Zend_Locale $baseLocale */
        $baseLocale = Shopware()->Container()->get('locale');

        $locales = Shopware()->Plugins()->Backend()->Auth()->getLocales();

        if (($key = array_search($targetLocale->getId(), $locales)) !== false) {
            unset($locales[$key]);
        }

        $locales = Shopware()->Db()->quote($locales);
        $sql = 'SELECT id, locale FROM s_core_locales WHERE id IN (' . $locales . ')';
        $locales = Shopware()->Db()->fetchPairs($sql);

        $data = array();
        foreach ($locales as $id => $locale) {
            list($l, $t) = explode('_', $locale);
            $l = $baseLocale->getTranslation($l, 'language', $targetLocale->getLocale());
            $t = $baseLocale->getTranslation($t, 'territory', $targetLocale->getLocale());
            $data[] = array(
                'id' => $id,
                'name' => "$l ($t)"
            );
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ));
    }

    /**
     * Shopware ID registration action for first run wizard
     *
     * Expects the following parameters in the request:
     * - shopwareID
     * - password
     * - email
     *
     * @throws Exception
     */
    public function registerNewIdAction()
    {
        $shopwareId = $this->Request()->getParam('shopwareID');
        $password = $this->Request()->getParam('password');
        $email = $this->Request()->getParam('email');

        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $locale = $this->getCurrentLocale();
            $accountManagerService->registerAccount($shopwareId, $email, $password, $locale->getId());
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign('message', 'loginSuccessful');

        $this->forward('login');
    }

    /**
     * Login action for first run wizard
     *
     * Expects the following parameters in the request:
     * - shopwareID
     * - password
     */
    public function loginAction()
    {
        $shopwareId = $this->Request()->getParam('shopwareID');
        $password = $this->Request()->getParam('password');

        try {
            $this->getToken($shopwareId, $password);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign('success', true);
        $message = $this->View()->getAssign('message');
        if (empty($message)) {
            $this->View()->assign('message', 'loginSuccessful');
        }
    }

    /**
     * Domain registration/retrieval action for first run wizard
     * Usually called after Shopware ID login/registration
     *
     * @throws Exception
     */
    public function registerDomainAction()
    {
        $shop = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Shop')
            ->getDefault();

        $shopwareId = $this->Request()->get('shopwareID');
        $password = $this->Request()->get('password');
        $domain = $shop->getHost();

        try {
            $token = $this->getToken($shopwareId, $password);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $domains = $this->getDomains($token);

        if (in_array($domain, $domains)) {
            $this->View()->assign(array(
                'success' => true,
                'message' => $this->get('snippets')
                    ->getNamespace('backend/first_run_wizard/main')
                    ->get('alreadyRegisteredDomain')
            ));
        }

        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $domainHashData = $accountManagerService->getDomainHash($domain, $token);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $filename = $domainHashData['fileName'];
        $fileContent = $domainHashData['content'];
        if (empty($filename) || empty($fileContent)) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Could not perform domain validation due to SBP error'
            ));
            return;
        }

        /** @var \Symfony\Component\Filesystem\Filesystem $fileSystem */
        $fileSystem = $this->container->get('file_system');
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $filePath = $rootDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $fileSystem->dumpFile($filePath, $fileContent);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        try {
            $accountManagerService->verifyDomain($domain, $this->getVersion(), $token);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            $fileSystem->remove($rootDir . DIRECTORY_SEPARATOR . $filename);
            return;
        }

        try {
            $fileSystem->remove($rootDir . DIRECTORY_SEPARATOR . $filename);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'message' => 'domainRegistered'
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

            try {
                /** @var LocaleStruct[] $serverLocales */
                $serverLocales = $accountManagerService->getLocales();
            } catch (Exception $e) {
                $this->View()->assign(array(
                    'success' => false,
                    'message' => $e->getMessage()
                ));
                return;
            }

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

    /**
     * Fetches shop domains for the current id
     *
     * @param AccessTokenStruct $token
     * @throws Exception
     * @return string[] Information about the current user's shop domains
     */
    private function getDomains(AccessTokenStruct $token)
    {
        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $shopsData = $accountManagerService->getShops($token);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $shopsDomains = array_map(function ($shopData) {
            return $shopData->domain;
        }, $shopsData);

        return $shopsDomains;
    }

    /**
     * Loads the SBP token from current session
     * If no valid token is available, queries the server for a new one
     *
     * @param string $shopwareId
     * @param string $password
     * @return AccessTokenStruct Token to access the API
     * @throws \RuntimeException
     */
    private function getToken($shopwareId, $password)
    {
        /** @var AccessTokenStruct $token */
        $token = Shopware()->BackendSession()->accessToken;

        if (empty($token) || $token->getExpire()->getTimestamp() <= strtotime("+30 seconds")) {
            if (empty($shopwareId) || empty($password)) {
                throw new \RuntimeException('Could not login - missing login data');
            }

            /** @var AccountManagerService $accountManagerService */
            $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

            $token = $accountManagerService->getToken($shopwareId, $password);

            Shopware()->BackendSession()->accessToken = $token;
        }

        return $token;
    }
}
