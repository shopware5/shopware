<?php

use Shopware\Bundle\StoreFrontBundle;

class Shopware_Controllers_Backend_FirstRunWizard extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Saves the current wizard step to the database
     */
    public function saveStepAction()
    {
        $value = (int) $this->Request()->getParam('value');
        $element = Shopware()->Models()
            ->getRepository('Shopware\Models\Config\Element')
            ->findOneBy(array('name' => 'firstRunWizardStep'));

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
            'desktopLogo',
            '_brand-primary',
            '_brand-secondary'
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
            'taxNumber',
            'bankAccount',
            'company',
            'metaIsFamilyFriendly',
            'captchaColor'
        ];

        $shopConfigValues = array_intersect_key($values, array_flip($shopConfigKeys));

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
            'desktopLogo',
            '_brand-primary',
            '_brand-secondary'
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
            'taxNumber',
            'bankAccount',
            'company',
            'metaIsFamilyFriendly',
            'captchaColor'
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
        /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
        $storeConnector = $this->container->get('plugin_store_connector');

        try {
            $isConnected = $storeConnector->ping();
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

        /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
        $storeConnector = $this->container->get('plugin_store_connector');

        try {
            $locale = $this->getCurrentLocale();
            $storeConnector->register($shopwareId, $email, $password, $locale->id);
        } catch (Exception $e) {
            return $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
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
                'message' => 'Authentication failed - '. $e->getMessage()
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

        $shopwareId = $this->Request()->getParam('shopwareID');
        $password = $this->Request()->getParam('password');
        $domain = 'http://'. $shop->getHost() . $shop->getBasePath();

        try {
            $token = $this->getToken($shopwareId, $password);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Authentication failed - '. $e->getMessage()
            ));
            return;
        }

        $domains = $this->getDomains($token, $shopwareId);

        if (in_array($domain, $domains)) {
            $this->View()->assign(array(
                'success' => true,
                'message' => 'alreadyRegisteredDomain'
            ));
        }

        /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
        $storeConnector = $this->container->get('plugin_store_connector');

        try {
            $domainHashData = $storeConnector->getDomainHash($domain, $token);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $filename = $domainHashData->fileName;
        $fileContent = $domainHashData->content;
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

        try {
            $fileSystem->dumpFile($rootDir . DIRECTORY_SEPARATOR . $domainHashData->fileName, $domainHashData->content);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        try {
            $storeConnector->verifyDomain($domain, $shopwareId, $token);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        try {
            $fileSystem->remove($rootDir . DIRECTORY_SEPARATOR . $domainHashData->fileName);
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
     * Fetches known server locales. Returns a struct in server format containing
     * info about the current user's locale.
     *
     * @return Obj Information about the current locale
     * @throws Exception
     */
    private function getCurrentLocale()
    {
        static $locales;

        if (empty($locales)) {
            /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
            $storeConnector = $this->container->get('plugin_store_connector');

            $serverLocales = $storeConnector->getLocales();

            foreach ($serverLocales as $serverLocale) {
                $locales[$serverLocale->name] = $serverLocale;
            }
        }

        $user = Shopware()->Auth()->getIdentity();
        /** @var $locale \Shopware\Models\Shop\Locale */
        $locale = $user->locale;
        $localeCode = $locale->getLocale();

        return array_key_exists($localeCode, $locales) ? $locales[$localeCode] : null;
    }

    /**
     * Fetches shop domains for the current id
     *
     * @param $token
     * @param $shopwareId
     * @throws Exception
     * @return string[] Information about the current user's shop domains
     */
    private function getDomains($token, $shopwareId)
    {
        /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
        $storeConnector = $this->container->get('plugin_store_connector');

        try {
            $shopsData = $storeConnector->getShops($token, $shopwareId);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $shopsDomains = array_map(function ($shopData) { return $shopData->domain; }, $shopsData);

        return $shopsDomains;
    }

    /**
     * Loads the SBP token from current session
     * If no valid token is available, queries the server for a new one
     *
     * @param string $shopwareId
     * @param string $password
     * @return string Token to access the API
     * @throws \RuntimeException
     */
    private function getToken($shopwareId, $password)
    {
        $tokenData = Shopware()->BackendSession()->accessToken;

        if (empty($tokenData) || strtotime($tokenData->expire->date) >= strtotime("+30 seconds")) {
            if (empty($shopwareId) || empty($password)) {
                throw new \RuntimeException('Could not login - missing login data');
            }

            /** @var \Shopware\Components\PluginStore\PluginStoreConnector $storeConnector */
            $storeConnector = $this->container->get('plugin_store_connector');

            $tokenData = $storeConnector->getToken($shopwareId, $password);

            Shopware()->BackendSession()->accessToken = $tokenData;
        }

        return $tokenData->token;
    }
}
