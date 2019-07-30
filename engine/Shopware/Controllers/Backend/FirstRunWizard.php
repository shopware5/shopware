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
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Models\Document\Element;

class Shopware_Controllers_Backend_FirstRunWizard extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions(): array
    {
        return [
            'payPalStartView',
        ];
    }

    /**
     * Saves the current wizard status (enabled/disabled) to the database
     */
    public function saveEnabledAction()
    {
        $value = (bool) $this->Request()->getParam('value');
        $element = Shopware()->Models()
            ->getRepository('Shopware\Models\Config\Element')
            ->findOneBy(['name' => 'firstRunWizardEnabled']);

        $defaultShop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getDefault();

        $requestElements = [
            [
                'id' => $element->getId(),
                'name' => $element->getName(),
                'values' => [
                    [
                        'value' => $value,
                        'shopId' => $defaultShop->getId(),
                    ],
                ],
                'type' => 'number',
            ],
        ];

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

        if (strpos($values['desktopLogo'], 'media/') === 0) {
            $values['tabletLandscapeLogo'] = $values['desktopLogo'];
            $values['tabletLogo'] = $values['desktopLogo'];
            $values['mobileLogo'] = $values['desktopLogo'];
            $values['emailheaderhtml'] = $values['desktopLogo'];
            $values['__document_logo'] = $values['desktopLogo'];
        }

        /**
         * Save theme config
         */
        $themeConfigKeys = [
            'desktopLogo',
            'tabletLandscapeLogo',
            'tabletLogo',
            'mobileLogo',
            'brand-primary',
            'brand-secondary',
        ];

        $themeConfigValues = array_map(function ($configKey) use ($defaultShop, $values) {
            return [
                'elementName' => $configKey,
                'shopId' => $defaultShop->getId(),
                'value' => array_key_exists($configKey, $values) ? $values[$configKey] : '',
            ];
        }, $themeConfigKeys);

        $theme = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Template')
            ->findOneBy(['template' => 'Responsive']);

        $themeConfigValues = array_filter($themeConfigValues, function ($config) {
            return !empty($config['value']);
        });

        $this->container->get('theme_service')->saveConfig($theme, $themeConfigValues);
        $this->container->get('theme_timestamp_persistor')->updateTimestamp($defaultShop->getId(), time());

        /**
         * Save shop config
         */
        $shopConfigKeys = [
            'shopName',
            'mail',
            'address',
            'bankAccount',
            'company',
            'emailheaderhtml',
        ];

        $shopConfigValues = array_intersect_key($values, array_flip($shopConfigKeys));

        $requestElements = [];

        foreach ($shopConfigValues as $configName => $configValue) {
            $element = Shopware()->Models()
                ->getRepository('Shopware\Models\Config\Element')
                ->findOneBy(['name' => $configName]);

            if ($configName === 'emailheaderhtml') {
                if (empty($configValue)) {
                    continue;
                }

                $configValue = sprintf(
                    "<div>\n<img src=\"{media path='%s'}\" style=\"max-height: 20mm\" alt=\"Logo\"><br />",
                    $configValue
                );
            }

            $requestElements[] = [
                'id' => $element->getId(),
                'name' => $element->getName(),
                'values' => [
                    [
                        'value' => $configValue,
                        'shopId' => $defaultShop->getId(),
                    ],
                ],
                'type' => 'number',
            ];
        }

        $this->Request()->setParam('elements', $requestElements);

        /**
         * Save document config
         */
        $documentConfigKeys = [
            'Logo',
        ];

        $documentConfigKeys = array_map(function ($key) {
            return '__document_' . strtolower($key);
        }, $documentConfigKeys);

        $documentConfigValues = array_intersect_key($values, array_flip($documentConfigKeys));
        $persistElements = [];

        foreach ($documentConfigValues as $key => $value) {
            $key = str_replace('__document_', '', $key);
            $elements = Shopware()->Models()->getRepository(Element::class)->findBy(['name' => $key]);

            if (empty($elements) || empty($value)) {
                continue;
            }

            if ($key === 'logo') {
                $hash = \Shopware\Components\Random::getAlphanumericString(16);
                $value = sprintf(
                    '<p><img id="tinymce-editor-image-%s" class="tinymce-editor-image tinymce-editor-image-%s" src="{media path=\'%s\'}" style="max-height: 20mm;" data-src="%s" /></p>',
                    $hash,
                    $hash,
                    $value,
                    $value
                );
            }

            foreach ($elements as $element) {
                $element->setValue($value);
                $persistElements[] = $element;
            }
        }

        if (count($persistElements)) {
            Shopware()->Models()->flush($persistElements);
        }

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
            ->findOneBy(['template' => 'Responsive']);

        /**
         * Load theme config values
         */
        $themeConfigKeys = [
            'desktopLogo',
            'brand-primary',
            'brand-secondary',
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
            'shopName',
            'mail',
            'address',
            'bankAccount',
            'company',
        ];

        $builder = $this->container->get('models')->createQueryBuilder();
        $builder->select([
            'elements',
            'values',
        ])
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

        $this->View()->assign([
            'success' => true,
            'data' => array_merge($shopConfigValues, $themeConfigValues),
        ]);
    }

    /**
     * Tests connectivity to SBP server
     *
     * @throws Exception
     */
    public function pingServerAction()
    {
        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $isConnected = $accountManagerService->pingServer();
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'message' => $isConnected,
        ]);
    }

    /**
     * Gets the available backend locales excluding the current one
     */
    public function getAlternativeLocalesAction()
    {
        /** @var \Shopware\Models\Shop\Locale $targetLocale */
        $targetLocale = Shopware()->Container()->get('auth')->getIdentity()->locale;

        $locales = Shopware()->Plugins()->Backend()->Auth()->getLocales();

        if (($key = array_search($targetLocale->getId(), $locales)) !== false) {
            unset($locales[$key]);
        }

        $locales = Shopware()->Db()->quote($locales);
        $sql = 'SELECT id, locale FROM s_core_locales WHERE id IN (' . $locales . ')';
        $locales = Shopware()->Db()->fetchPairs($sql);

        $data = [];
        foreach ($locales as $id => $locale) {
            list($l, $t) = explode('_', $locale);
            $l = Zend_Locale::getTranslation($l, 'language', $targetLocale->getLocale());
            $t = Zend_Locale::getTranslation($t, 'territory', $targetLocale->getLocale());
            $data[] = [
                'id' => $id,
                'name' => "$l ($t)",
            ];
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
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
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

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
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

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
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $domains = $this->getDomains($token);

        if (in_array($domain, $domains)) {
            $this->View()->assign([
                'success' => true,
                'message' => $this->get('snippets')
                    ->getNamespace('backend/first_run_wizard/main')
                    ->get('alreadyRegisteredDomain'),
            ]);
        }

        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $domainHashData = $accountManagerService->getDomainHash($domain, $token);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $filename = $domainHashData['fileName'];
        $fileContent = $domainHashData['content'];
        if (empty($filename) || empty($fileContent)) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Could not perform domain validation due to SBP error',
            ]);

            return;
        }

        /** @var \Symfony\Component\Filesystem\Filesystem $fileSystem */
        $fileSystem = $this->container->get('file_system');
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $filePath = $rootDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $fileSystem->dumpFile($filePath, $fileContent);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        try {
            $accountManagerService->verifyDomain($domain, $this->getVersion(), $token);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
            $fileSystem->remove($rootDir . DIRECTORY_SEPARATOR . $filename);

            return;
        }

        try {
            $fileSystem->remove($rootDir . DIRECTORY_SEPARATOR . $filename);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'message' => 'domainRegistered',
        ]);
    }

    /**
     * The payPalStartViewAction renders the start screen of the PayPal integration in the
     * First-Run-Wizard.
     */
    public function payPalStartViewAction(): void
    {
        $this->renderPayPalView('start');
    }

    /**
     * renderPayPalView is a helper-method to render templates for the PayPal integration in
     * the First-Run-Wizard.
     */
    private function renderPayPalView(string $view): void
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender(false);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $this->View()->loadTemplate(sprintf('backend/first_run_wizard/template/%s.tpl', $view));
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        return $this->container->getParameter('shopware.release.version');
    }

    /**
     * Fetches known server locales. Returns a struct in server format containing
     * info about the current user's locale.
     *
     * @throws Exception
     *
     * @return LocaleStruct|null Information about the current locale
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
                $this->View()->assign([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);

                return null;
            }

            foreach ($serverLocales as $serverLocale) {
                $locales[$serverLocale->getName()] = $serverLocale;
            }
        }

        $user = Shopware()->Container()->get('auth')->getIdentity();
        /** @var \Shopware\Models\Shop\Locale $locale */
        $locale = $user->locale;
        $localeCode = $locale->getLocale();

        return array_key_exists($localeCode, $locales) ? $locales[$localeCode] : null;
    }

    /**
     * Fetches shop domains for the current id
     *
     * @throws Exception
     *
     * @return string[]|null Information about the current user's shop domains
     */
    private function getDomains(AccessTokenStruct $token)
    {
        /** @var AccountManagerService $accountManagerService */
        $accountManagerService = $this->container->get('shopware_plugininstaller.account_manager_service');

        try {
            $shopsData = $accountManagerService->getShops($token);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return null;
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
     *
     * @throws \RuntimeException
     *
     * @return AccessTokenStruct Token to access the API
     */
    private function getToken($shopwareId, $password)
    {
        /** @var AccessTokenStruct $token */
        $token = Shopware()->BackendSession()->accessToken;

        if (empty($token) || $token->getExpire()->getTimestamp() <= strtotime('+30 seconds')) {
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
