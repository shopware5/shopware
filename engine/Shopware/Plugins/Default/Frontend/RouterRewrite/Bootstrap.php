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
 *  Shopware Router Rewrite Plugin
 */
class Shopware_Plugins_Frontend_RouterRewrite_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function getCapabilities()
    {
        return [
            'install' => false,
            'update' => false,
            'enable' => false,
            'secureUninstall' => false,
        ];
    }

    /**
     * Install plugin method
     *
     * Registers the plugin start event.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );

        return true;
    }

    /**
     * Loads the plugin before the dispatch.
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        $event = new Enlight_Event_Handler_Default(
            'Enlight_Controller_Front_PreDispatch',
            [$this, 'onPreDispatch']
        );
        Shopware()->Events()->registerListener($event);
    }

    /**
     * Checks the url / the request and passes it around if necessary.
     */
    public function onPreDispatch(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        if ($response->isException()
            || $request->isPost()
            || $request->isXmlHttpRequest()             // is a ajax call
            || $request->has('callback')                // is a jsonp call
            || $request->getModuleName() != 'frontend'  // is not frontend
            || !$request->getParam('rewriteAlias')      // is not a rewrite url alias
        ) {
            return;
        }
        $router = $args->getSubject()->Router();

        $query = $request->getQuery();
        $location = $router->assemble($query);

        // Fix shop redirect / if it's not a seo url
        if (preg_match('#\/[0-9]+$#', $location, $match) > 0) {
            $location = $request->getBaseUrl() . '/';
        }

        $current = $request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri();
        if ($location !== $current) {
            $response->setRedirect($location, 301);
        }
    }
}
