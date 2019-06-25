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

        $container = Shopware()->Container();
        if (!$container->initialized('session')) {
            return;
        }

        /** @var Shopware_Plugins_Frontend_Statistics_Bootstrap $plugin */
        $plugin = Shopware()->Plugins()->Frontend()->Statistics();
        if ($plugin->checkIsBot($args->getRequest()->getHeader('USER_AGENT'))) {
            Enlight_Components_Session::destroy(true, false);
        }
    }

    /**
     * Event listener method
     *
     * @return \sSystem
     */
    public function onInitResourceSystem(Enlight_Event_EventArgs $args)
    {
        $config = Shopware()->Config();

        $request = Shopware()->Front()->Request();
        $system = new sSystem($request);

        Shopware()->Container()->set('system', $system);

        $system->sMODULES = Shopware()->Modules();
        $system->sSMARTY = Shopware()->Template();
        $system->sCONFIG = $config;
        $system->sMailer = Shopware()->Container()->get('mail');

        if (Shopware()->Container()->initialized('session')) {
            $system->_SESSION = Shopware()->Session();
            $system->sSESSION_ID = Shopware()->Session()->get('sessionId');
            if ($request !== null && Shopware()->Session()->Bot === null) {
                /** @var Shopware_Plugins_Frontend_Statistics_Bootstrap $plugin */
                $plugin = Shopware()->Plugins()->Frontend()->Statistics();
                Shopware()->Session()->Bot = $plugin->checkIsBot($request->getHeader('USER_AGENT'));
            }
            $system->sBotSession = Shopware()->Session()->Bot;
        }

        if (Shopware()->Container()->initialized('shop')) {
            $shop = Shopware()->Shop();
            $system->sCurrency = $shop->getCurrency()->toArray();

            $system->sUSERGROUP = $shop->getCustomerGroup()->getKey();
            $system->sUSERGROUPDATA = $shop->getCustomerGroup()->toArray();
            $config->defaultCustomerGroup = $system->sUSERGROUP;
        }

        if (Shopware()->Container()->initialized('session')) {
            if (!empty(Shopware()->Session()->sUserGroup)
                    && Shopware()->Session()->sUserGroup != $system->sUSERGROUP) {
                $system->sUSERGROUP = Shopware()->Session()->sUserGroup;
                $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow('
                    SELECT * FROM s_core_customergroups
                    WHERE groupkey = ?
                ', [$system->sUSERGROUP]);
            }
            if (empty($system->sUSERGROUPDATA['tax']) && !empty($system->sUSERGROUPDATA['id'])) {
                $config['sARTICLESOUTPUTNETTO'] = 1; //Old template
                Shopware()->Session()->sOutputNet = true;
            } else {
                Shopware()->Session()->sOutputNet = false;
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
     * @return \Shopware_Components_Modules
     */
    public static function onInitResourceModules(Enlight_Event_EventArgs $args)
    {
        $modules = new Shopware_Components_Modules();
        Shopware()->Container()->set('Modules', $modules);
        $modules->setSystem(Shopware()->System());

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
