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

use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    const MIN_DAYS_INSTALLATION_SURVEY = 14;
    const MIN_DAYS_BI_TEASER = 10;

    /**
     * @var Shopware_Plugins_Backend_Auth_Bootstrap
     */
    protected $auth;

    /**
     * Loads auth and script renderer resource
     */
    public function init()
    {
        $this->auth = Shopware()->Plugins()->Backend()->Auth();
        $this->auth->setNoAuth();
        $this->Front()->Plugins()->ScriptRenderer()->setRender();
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
            'auth',
            'changeLocale',
            'load',
            'menu',
        ];
    }

    /**
     * Activate caching, set backend redirect
     *
     * @throws Exception
     */
    public function preDispatch()
    {
        // Redirect broken backend urls to frontend
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'menu', 'auth', 'changeLocale'])) {
            $uri = $this->Request()->getRequestUri();
            $uri = str_replace(['shopware.php/', '/backend/'], ['', '/'], $uri);
            $this->redirect($uri, ['code' => 301]);

            return;
        }

        if (strpos($this->Request()->getPathInfo(), '/backend/') !== 0) {
            $this->redirect('backend/', ['code' => 301]);
        }

        $this->View()->assign('esEnabled', $this->container->getParameter('shopware.es.backend.enabled'));
    }

    /**
     * On index - get all Resources that we need in backend area
     * Backend Menu
     * Licence Information
     * Rss-Data for example
     */
    public function indexAction()
    {
        // Script renderer
        if ($this->Request()->getParam('file') !== null) {
            return;
        }

        // Check session
        try {
            $auth = $this->auth->checkAuth();
        } catch (Exception $e) {
            $auth = null;
        }

        // No session
        if ($auth === null) {
            $this->forward('auth', 'index', 'backend');

            return;
        }

        $identity = $auth->getIdentity();

        $this->View()->assign('user', $identity, true);
        $app = $this->Request()->getParam('app', 'Index');
        $this->View()->assign('app', $app, true);

        $params = $this->Request()->getParam('params', []);
        $params = Zend_Json::encode($params);
        $this->View()->assign('params', $params, true);

        $controller = $this->Request()->getParam('controller');
        $controller = Zend_Json::encode($controller);
        $this->View()->assign('controller', $controller, true);

        $this->View()->assign('maxParameterLength', (int) ini_get('suhosin.get.max_value_length') + 0, true);

        $firstRunWizardEnabled = $this->isFirstRunWizardEnabled($identity);
        $sbpLogin = 0;
        if ($firstRunWizardEnabled) {
            /** @var \Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct $tokenData */
            $tokenData = Shopware()->BackendSession()->accessToken;

            $sbpLogin = (int) (!empty($tokenData) && $tokenData->getExpire() >= new DateTime('+30 seconds'));
        }
        $this->View()->assign('sbpLogin', $sbpLogin, true);
        $this->View()->assign('firstRunWizardEnabled', $firstRunWizardEnabled, true);
        $this->View()->assign('installationSurvey', $this->checkForInstallationSurveyNecessity($identity), true);

        /** @var Shopware_Components_Config $config */
        $config = $this->get('config');

        /** @var \Shopware\Components\ShopwareReleaseStruct $shopwareRelease */
        $shopwareRelease = $this->container->get('shopware.release');

        $this->View()->assign('SHOPWARE_VERSION', $shopwareRelease->getVersion());
        $this->View()->assign('SHOPWARE_VERSION_TEXT', $shopwareRelease->getVersionText());
        $this->View()->assign('SHOPWARE_REVISION', $shopwareRelease->getRevision());
        $this->View()->assign('updateWizardStarted', $config->get('updateWizardStarted'));
        $this->View()->assign('feedbackRequired', $this->checkIsFeedbackRequired());
        $this->View()->assign('biOverviewEnabled', $this->isBIOverviewEnabled());
        $this->View()->assign('biIsActive', $this->isBIActive());
        $this->View()->assign('extJsDeveloperModeActive', $this->container->getParameter('shopware.extjs.developer_mode'));
    }

    public function authAction()
    {
    }

    /**
     * Allows changing the locale by sending a Shopware localeId or an ISO-3166 locale (e.g. de_DE)
     */
    public function changeLocaleAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $localeId = $this->Request()->getParam('localeId');
        if (!$localeId) {
            $this->View()->assign([
                'success' => false,
                'message' => false,
            ]);

            return;
        }

        $localeRepository = $this->container->get('models')
            ->getRepository(Shopware\Models\Shop\Locale::class);

        $locale = $localeRepository->find($localeId);

        if (!$locale) {
            $locale = $localeRepository->findBy(['locale' => $localeId]);

            if ($locale && count($locale) === 1) {
                $locale = $locale[0];
            }
        }

        if (!$locale) {
            $this->View()->assign([
                'success' => false,
                'message' => false,
            ]);

            return;
        }

        $auth = $this->auth->checkAuth();

        if ($auth !== null) {
            $identity = $auth->getIdentity();
            if (!empty($identity)) {
                $identity->locale = $locale;

                $this->View()->assign([
                    'success' => true,
                    'message' => true,
                ]);
            }
        }
    }

    /**
     * Load action for the script renderer.
     *
     * @throws Enlight_Controller_Exception
     */
    public function loadAction()
    {
        $auth = $this->auth->checkAuth();
        if ($auth === null) {
            throw new \Enlight_Controller_Exception('Unauthorized', 401);
        }
        /** @var \Shopware\Components\ShopwareReleaseStruct $shopwareRelease */
        $shopwareRelease = $this->container->get('shopware.release');

        $this->View()->assign('SHOPWARE_VERSION', $shopwareRelease->getVersion());
        $this->View()->assign('SHOPWARE_VERSION_TEXT', $shopwareRelease->getVersionText());
        $this->View()->assign('SHOPWARE_REVISION', $shopwareRelease->getRevision());
    }

    /**
     * Load action for the script renderer.
     *
     * @throws Enlight_Controller_Exception
     */
    public function menuAction()
    {
        if ($this->auth->checkAuth() === null) {
            throw new \Enlight_Controller_Exception('Unauthorized', 401);
        }

        /** @var \Shopware\Models\Menu\Repository $menu */
        $menu = Shopware()->Models()->getRepository(\Shopware\Models\Menu\Menu::class);
        $nodes = $menu->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('m.plugin', 'p')
            ->where('m.active = 1')
            ->andWhere('m.pluginId IS NULL OR p.active = 1')
            ->orderBy('m.parentId', 'ASC')
            ->addOrderBy('m.position', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $menuItems = $this->buildTree($nodes);
        $this->View()->assign('menu', $menuItems);
    }

    /**
     * Returns if the first run wizard should be loaded in the current backend instance
     *
     * @param stdClass $identity
     *
     * @return bool
     */
    private function isFirstRunWizardEnabled($identity)
    {
        // Only admins can see the wizard
        if ($identity->role->getAdmin()) {
            return $this->container->get('config')->get('firstRunWizardEnabled', false);
        }

        return false;
    }

    /**
     * @param int|null $parentId
     *
     * @return array
     */
    private function buildTree(array $nodes, $parentId = null)
    {
        $menuTree = [];
        foreach ($nodes as $key => $node) {
            if ($node['parentId'] == $parentId) {
                $subTree = $this->buildTree($nodes, $node['id']);
                if ($subTree) {
                    $node['children'] = $subTree;
                }
                $menuTree[] = $node;
            }
        }

        return $menuTree;
    }

    /**
     * @return bool
     */
    private function checkIsFeedbackRequired()
    {
        $shopwareVersionText = $this->container->getParameter('shopware.release.version_text');

        return !in_array($shopwareVersionText, ['', '___VERSION_TEXT___'], true);
    }

    /**
     * @param stdClass $identity
     *
     * @return bool
     */
    private function checkForInstallationSurveyNecessity($identity)
    {
        if ($this->checkIsFeedbackRequired() || !$identity->role->getAdmin()) {
            return false;
        }
        $installationSurvey = $this->container->get('config')->get('installationSurvey', false);
        $installationDate = \DateTime::createFromFormat('Y-m-d H:i', $this->container->get('config')->get('installationDate'));
        if (!$installationSurvey || !$installationDate) {
            return false;
        }
        $interval = $installationDate->diff(new \DateTime());

        return $interval->days >= self::MIN_DAYS_INSTALLATION_SURVEY;
    }

    /**
     * @return bool
     */
    private function isBIOverviewEnabled()
    {
        if (!$this->get('config')->get('benchmarkTeaser')) {
            return false;
        }

        /** @var \Shopware\Models\Benchmark\Repository $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');

        $shopwareVersionText = $this->container->getParameter('shopware.release.version_text');

        $waitingOver = true;
        $installationDate = \DateTime::createFromFormat('Y-m-d H:i', $this->container->get('config')->get('installationDate'));
        if ($installationDate) {
            $interval = $installationDate->diff(new \DateTime());

            if ($interval->days < self::MIN_DAYS_BI_TEASER) {
                $waitingOver = false;
            }
        }

        return $waitingOver && $shopwareVersionText !== '___VERSION_TEXT___' && $configRepository->getConfigsCount() === 0;
    }

    /**
     * @return bool
     */
    private function isBIActive()
    {
        /** @var \Shopware\Models\Benchmark\Repository $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');

        $validShopCount = count($configRepository->getValidShops());

        return $validShopCount > 0;
    }
}
