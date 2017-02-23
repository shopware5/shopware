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

/**
 * Shopware Backend Controller
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    const MIN_DAYS_INSTALLATION_SURVEY = 14;

    /**
     * @var \Shopware\Components\Auth\BackendAuthSubscriber
     */
    protected $auth;

    /**
     * Loads auth and script renderer resource
     */
    public function init()
    {
        $this->auth = Shopware()->Container()->get('shopware.subscriber.auth');
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
     */
    public function preDispatch()
    {
        // Redirect broken backend urls to frontend
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'menu', 'auth', 'changeLocale'])) {
            $uri = $this->Request()->getRequestUri();
            $uri = str_replace('shopware.php/', '', $uri);
            $uri = str_replace('/backend/', '/', $uri);
            $this->redirect($uri, ['code' => 301]);

            return;
        }

        if (strpos($this->Request()->getPathInfo(), '/backend/') !== 0) {
            $this->redirect('backend/', ['code' => 301]);
        }
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
            $tokenData = Shopware()->Container()->get('backend_session')->accessToken;

            $sbpLogin = (int) (!empty($tokenData) && $tokenData->getExpire() >= new DateTime('+30 seconds'));
        }
        $this->View()->assign('sbpLogin', $sbpLogin, true);
        $this->View()->assign('firstRunWizardEnabled', $firstRunWizardEnabled, true);
        $this->View()->assign('installationSurvey', $this->checkForInstallationSurveyNecessity($identity), true);

        /** @var Shopware_Components_Config $config */
        $config = $this->get('config');

        $this->View()->assign('updateWizardStarted', $config->get('updateWizardStarted'));
        $this->View()->assign('feedbackRequired', $this->checkIsFeedbackRequired());
    }

    public function authAction()
    {
    }

    public function changeLocaleAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $localeId = $this->Request()->getParam('localeId');
        if ($localeId == null) {
            $this->View()->assign([
                'success' => false,
                'message' => false,
            ]);

            return;
        }

        $locale = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Locale')
            ->find($localeId);

        if ($locale == null) {
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
     */
    public function loadAction()
    {
        $auth = $this->auth->checkAuth();
        if ($auth === null) {
            throw new Enlight_Controller_Exception('Unauthorized', 401);
        }
    }

    /**
     * Load action for the script renderer.
     */
    public function menuAction()
    {
        if ($this->auth->checkAuth() === null) {
            throw new Enlight_Controller_Exception('Unauthorized', 401);
        }

        /** @var $menu \Shopware\Models\Menu\Repository */
        $menu = Shopware()->Models()->getRepository('Shopware\Models\Menu\Menu');
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
        $this->View()->menu = $menuItems;
    }

    /**
     * Returns if the first run wizard should be loaded in the current backend instance
     *
     * @param stdClass $identity
     *
     * @throws Exception
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
     * @param array    $nodes
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
        return Shopware::VERSION_TEXT !== '___VERSION_TEXT___' && strlen(Shopware::VERSION_TEXT) !== 0;
    }

    /**
     * @param stdClass $identity
     *
     * @return bool
     */
    private function checkForInstallationSurveyNecessity($identity)
    {
        if (!$identity->role->getAdmin() || Shopware::VERSION_TEXT === '___VERSION_TEXT___') {
            return false;
        }
        $installationSurvey = $this->container->get('config')->get('installationSurvey', false);
        $installationDate = \DateTime::createFromFormat('Y-m-d H:i', $this->container->get('config')->get('installationDate'));
        if (!$installationSurvey || !$installationDate) {
            return false;
        }
        $now = new \DateTime();
        $interval = $installationDate->diff($now);

        return self::MIN_DAYS_INSTALLATION_SURVEY <= $interval->days;
    }
}
