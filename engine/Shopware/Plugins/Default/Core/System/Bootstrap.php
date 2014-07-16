<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package   Shopware\Plugins\Core
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
            'Enlight_Bootstrap_InitResource_Adodb',
            'onInitResourceAdodb'
        );
        return true;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     * @return \sSystem
     */
    public static function onInitResourceSystem(Enlight_Event_EventArgs $args)
    {
        $config = Shopware()->Config();

        $request = Shopware()->Front()->Request();
        $system = new sSystem($request);
        Shopware()->Bootstrap()->registerResource('System', $system);

        $system->sMODULES = Shopware()->Modules();
        $system->sDB_CONNECTION = Shopware()->Adodb();
        $system->sSMARTY = Shopware()->Template();
        $system->sCONFIG = $config;
        $system->sMailer = Shopware()->Mail();


        if (Shopware()->Bootstrap()->issetResource('Session')) {
            $system->_SESSION = Shopware()->Session();
            $system->sSESSION_ID = Shopware()->SessionID();
            if (Shopware()->Session()->Bot === null) {
                /** @var $plugin Shopware_Plugins_Frontend_Statistics_Bootstrap */
                $plugin = Shopware()->Plugins()->Frontend()->Statistics();
                Shopware()->Session()->Bot = $plugin->checkIsBot($request->getHeader('USER_AGENT'));
            }
            $system->sBotSession = Shopware()->Session()->Bot;
        }

        if (Shopware()->Bootstrap()->issetResource('Shop')) {
            $shop = Shopware()->Shop();
            $system->sSubShops = self::getShopData();
            $system->sLanguageData = $system->sSubShops;

            $system->sLanguage = $shop->getId();
            $system->sSubShop = $system->sSubShops[$shop->getId()];
            $system->sCurrency = $shop->getCurrency()->toArray();

            $system->sUSERGROUP = $shop->getCustomerGroup()->getKey();
            $system->sUSERGROUPDATA = $shop->getCustomerGroup()->toArray();
            $config->defaultCustomerGroup = $system->sUSERGROUP;
        }

        if (Shopware()->Bootstrap()->issetResource('Session')) {
            if (!empty(Shopware()->Session()->sUserGroup)
                    && Shopware()->Session()->sUserGroup != $system->sUSERGROUP) {
                $system->sUSERGROUP = Shopware()->Session()->sUserGroup;
                //$system->sUSERGROUPDATA = Shopware()->Session()->sUserGroupData;
                $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow("
                    SELECT * FROM s_core_customergroups
                    WHERE groupkey = ?
                ", array($system->sUSERGROUP));
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
        $system->sPathArticleFiles = $sPathBase . $config->articleFiles;

        $config['sCURRENCY'] = $system->sCurrency['currency'];
        $config['sCURRENCYHTML'] = $system->sCurrency['symbol'];

        return $system;
    }

    /**
     * Returns shop data
     *
     * @return array
     */
    public static function getShopData()
    {
        $data = Shopware()->Db()->fetchAssoc('SELECT id as `key`, m.* FROM s_core_multilanguage m');
        return $data;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Components_Modules
     */
    public static function onInitResourceModules(Enlight_Event_EventArgs $args)
    {
        $modules = new Shopware_Components_Modules();
        Shopware()->Bootstrap()->registerResource('Modules', $modules);
        $modules->setSystem(Shopware()->System());

        return $modules;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Enlight_Components_Adodb
     */
    public static function onInitResourceAdodb(Enlight_Event_EventArgs $args)
    {
        $db = new Enlight_Components_Adodb(array(
            'db' => Shopware()->Db()
        ));

        return $db;
    }

    /**
     * Returns capabilities
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }
}
