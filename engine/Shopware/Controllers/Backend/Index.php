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

/**
 * Shopware Backend Controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Index extends Enlight_Controller_Action
{
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
     * Activate caching, set backend redirect
     */
    public function preDispatch()
    {
        // Redirect broken backend urls to frontend
        if (!in_array($this->Request()->getActionName(), array('index', 'load', 'menu', 'auth', 'changeLocale'))) {
            $uri = $this->Request()->getRequestUri();
            $uri = str_replace('shopware.php/', '', $uri);
            $uri = str_replace('/backend/', '/', $uri);
            $this->redirect($uri, array('code' => 301));

            return;
        }

        if (strpos($this->Request()->getPathInfo(), '/backend/') !== 0) {
            $this->redirect('backend/', array('code' => 301));
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

        $params = $this->Request()->getParam('params', array());
        $params = Zend_Json::encode($params);
        $this->View()->assign('params', $params, true);

        $controller = $this->Request()->getParam('controller');
        $controller = Zend_Json::encode($controller);
        $this->View()->assign('controller', $controller, true);

        $this->View()->assign('product', '', true);
        $this->View()->assign('maxParameterLength', (int) ini_get('suhosin.get.max_value_length') + 0, true);

        $firstRunWizardEnabled = $this->isFirstRunWizardEnabled($identity);
        $sbpLogin = 0;
        if ($firstRunWizardEnabled) {
            /** @var \Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct $tokenData */
            $tokenData = Shopware()->BackendSession()->accessToken;

            $sbpLogin = (int) (!empty($tokenData) && $tokenData->getExpire() >= new DateTime("+30 seconds"));
        }
        $this->View()->assign('sbpLogin', $sbpLogin, true);
        $this->View()->assign('firstRunWizardEnabled', $firstRunWizardEnabled, true);

        if (Shopware()->Bootstrap()->issetResource('License')) {
            $l = Shopware()->License();
            $m = 'SwagCommercial';
            $o = $l->getLicenseInfo($m);
            $r = isset($o['product']) ? $o['product'] : null;
            $this->View()->assign('product', $r, true);
        }

        /** @var Shopware_Components_Config $config */
        $config = $this->get('config');

        $this->View()->assign('updateWizardStarted', $config->get('updateWizardStarted'));
    }

    /**
     * Returns if the first run wizard should be loaded in the current backend instance
     *
     * @param $identity
     * @return bool
     * @throws Exception
     */
    private function isFirstRunWizardEnabled($identity)
    {
        // Only admins can see the wizard
        if ($identity->role->getAdmin()) {
            return $this->container->get('config')->get('firstRunWizardEnabled', false);
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function authAction()
    {
    }

    /**
     *
     */
    public function changeLocaleAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $localeId = $this->Request()->getParam('localeId');
        if ($localeId == null) {
            $this->View()->assign(array(
                'success' => false,
                'message' => false
            ));
            return;
        }

        $locale = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Locale')
            ->find($localeId);

        if ($locale == null) {
            $this->View()->assign(array(
                'success' => false,
                'message' => false
            ));
            return;
        }

        $auth = $this->auth->checkAuth();

        if ($auth !== null) {
            $identity = $auth->getIdentity();
            if (!empty($identity)) {
                $identity->locale = $locale;

                $this->View()->assign(array(
                    'success' => true,
                    'message' => true
                ));
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
        $menu = Shopware()->Models()->getRepository(
            'Shopware\Models\Menu\Menu'
        );
        $menuItems = $menu->findBy(array('parentId' => null), array('position' => 'ASC'));
        $this->View()->menu = $menuItems;
    }
}
