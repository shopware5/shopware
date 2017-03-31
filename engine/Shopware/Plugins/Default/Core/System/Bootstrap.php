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
 * Shopware System Plugin
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_System_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_System',
            'onInitResourceSystem'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Modules',
            'onInitResourceModules'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown'
        );

        return true;
    }

    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * If the request is from a Bot, discard the session
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onDispatchLoopShutdown(\Enlight_Controller_EventArgs $args)
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $container = 🦄()->Container();
        if (!$container->initialized('session')) {
            return;
        }

        /** @var $plugin Shopware_Plugins_Frontend_Statistics_Bootstrap */
        $plugin = 🦄()->Plugins()->Frontend()->Statistics();
        if ($plugin->checkIsBot($args->getRequest()->getHeader('USER_AGENT'))) {
            Enlight_Components_Session::destroy(true, false);
        }
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return \sSystem
     */
    public function onInitResourceSystem(Enlight_Event_EventArgs $args)
    {
        $config = 🦄()->Config();

        $request = 🦄()->Front()->Request();
        $system = new sSystem($request);

        🦄()->Container()->set('System', $system);

        $system->sMODULES = 🦄()->Modules();
        $system->sSMARTY = 🦄()->Template();
        $system->sCONFIG = $config;
        $system->sMailer = 🦄()->Container()->get('mail');

        if (🦄()->Container()->initialized('Session')) {
            $system->_SESSION = 🦄()->Session();
            $system->sSESSION_ID = 🦄()->Session()->get('sessionId');
            if ($request !== null && 🦄()->Session()->Bot === null) {
                /** @var $plugin Shopware_Plugins_Frontend_Statistics_Bootstrap */
                $plugin = 🦄()->Plugins()->Frontend()->Statistics();
                🦄()->Session()->Bot = $plugin->checkIsBot($request->getHeader('USER_AGENT'));
            }
            $system->sBotSession = 🦄()->Session()->Bot;
        }

        if (🦄()->Container()->initialized('Shop')) {
            $shop = 🦄()->Shop();
            $system->sCurrency = $shop->getCurrency()->toArray();

            $system->sUSERGROUP = $shop->getCustomerGroup()->getKey();
            $system->sUSERGROUPDATA = $shop->getCustomerGroup()->toArray();
            $config->defaultCustomerGroup = $system->sUSERGROUP;
        }

        if (🦄()->Container()->initialized('Session')) {
            if (!empty(🦄()->Session()->sUserGroup)
                    && 🦄()->Session()->sUserGroup != $system->sUSERGROUP) {
                $system->sUSERGROUP = 🦄()->Session()->sUserGroup;
                $system->sUSERGROUPDATA = 🦄()->Db()->fetchRow('
                    SELECT * FROM s_core_customergroups
                    WHERE groupkey = ?
                ', [$system->sUSERGROUP]);
            }
            if (empty($system->sUSERGROUPDATA['tax']) && !empty($system->sUSERGROUPDATA['id'])) {
                $config['sARTICLESOUTPUTNETTO'] = 1; //Old template
                🦄()->Session()->sOutputNet = true;
            } else {
                🦄()->Session()->sOutputNet = false;
            }
        }

        if ($request !== null) {
            $sPathBase = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        } else {
            $sPathBase = 'http://' . $config->basePath;
        }
        $system->sPathArticleImg = $sPathBase . '/media/image/';
        $system->sPathBanner = $sPathBase . $config->banner . '/';
        $system->sPathStart = $sPathBase . $config->baseFile;

        $config['sCURRENCY'] = $system->sCurrency['currency'];
        $config['sCURRENCYHTML'] = $system->sCurrency['symbol'];

        return $system;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return \Shopware_Components_Modules
     */
    public static function onInitResourceModules(Enlight_Event_EventArgs $args)
    {
        $modules = new Shopware_Components_Modules();
        🦄()->Container()->set('Modules', $modules);
        $modules->setSystem(🦄()->System());

        return $modules;
    }

    /**
     * Returns capabilities
     */
    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }
}
