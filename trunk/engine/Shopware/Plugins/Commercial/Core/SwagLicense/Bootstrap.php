<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Template Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Core_SwagLicense_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();

        $form = $this->Form();
        $form->setName('license');

        $translations = array(
            'en_GB' => 'License manager',
            'de_DE' => 'Lizenz-Manager'
        );

        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');

        foreach($translations as $locale => $snippet) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            if($localeModel === null){
                continue;
            }
            $translationModel = new \Shopware\Models\Config\FormTranslation();
            $translationModel->setLabel($snippet);
            $translationModel->setLocale($localeModel);
            $form->addTranslation($translationModel);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Lizenz-Manager';
    }
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.3';
    }

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_License',
            'onInitResourceLicense'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Index',
            'onPostDispatchBackendIndex'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onDispatchLoopStartup'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onPostDispatchBackendConfig'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_License',
            'onGetControllerPathBackend'
        );
    }

    /**
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagCommercial';
        if(!isset($r)) {
            $s = base64_decode('HxXzbjuwgns5D4TlHM+tV9K1svc=');
            $c = base64_decode('IPF8Dvf0oWT0jMP4wlz1oZ9H+Lc=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r, true);
            if($r) {
                $o = $l->getLicenseInfo($m);
                $r = isset($o['product']) ? $o['product'] : null;
            }
        }
        if(!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return Shopware_Components_License
     */
    public function onInitResourceLicense(Enlight_Event_EventArgs $args)
    {
        $this->registerMyComponentDir();
        $sql = 'SELECT module, host, license FROM s_core_licenses WHERE active=1';
        $list = $this->Application()->Db()->fetchAll($sql);
        $sql = 'SELECT host FROM s_core_shops WHERE `default`=1';
        $host = $this->Application()->Db()->fetchOne($sql);
        return new Shopware_Components_License($host, $list);
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $args)
    {
        /** @var $view Enlight_View_Default */
        $view = $args->getSubject()->View();
        if(!$view->hasTemplate()) {
            return;
        }
        $this->registerMyTemplateDir();
        $view->assign('product', $this->checkLicense(false));
        $view->extendsTemplate('backend/index/license.tpl');
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopStartup(Enlight_Event_EventArgs $args)
    {
        if(!$this->checkLicense(false)) {
            return;
        }
        // Register my library dir
        Shopware()->Loader()->addIncludePath(
            $this->Path() . 'Library/',
            Enlight_Loader::POSITION_PREPEND
        );
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackendConfig(Enlight_Event_EventArgs $args)
    {
        /** @var $action Shopware_Controllers_Backend_ExtJs */
        $action = $args->getSubject();
        $view = $action->View();
        if(!$view->hasTemplate()) {
            return;
        }
        $this->registerMyTemplateDir();
        $view->extendsTemplate('backend/config/controller/license.js');
    }

    /**
     * @param   Enlight_Event_EventArgs $args
     * @return  string
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
        $this->registerMyComponentDir();
        $this->registerMyTemplateDir();
        return $this->Path() . 'Controllers/Backend/License.php';
    }

    /**
     *
     */
    protected function registerMyTemplateDir()
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'license'
        );
    }

    /**
     *
     */
    protected function registerMyComponentDir()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );
    }
}
