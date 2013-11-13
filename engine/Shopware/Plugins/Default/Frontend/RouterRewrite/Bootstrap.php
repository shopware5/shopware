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
 * @subpackage Frontend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 * @author     Heiner Lohaus
 */

/**
 *  Shopware Router Rewrite Plugin
 */
class Shopware_Plugins_Frontend_RouterRewrite_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    protected $urlToLower = false;
    protected $inquiryId = null;
    protected $paths = array();
    protected $urls = array();

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
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        $config = Shopware()->Config();
        $this->urlToLower = !empty($config->routerToLower);
        $this->inquiryId = $config->inquiryId;

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            array($this, 'onAfterSendResponse')
        );
        Shopware()->Events()->registerListener($event);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Router_Route',
            array($this, 'onRoute')
        );
        Shopware()->Events()->registerListener($event);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Router_Assemble',
            array($this, 'onAssemble')
        );
        Shopware()->Events()->registerListener($event);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Router_Assemble',
            array($this, 'onAssemble')
        );
        Shopware()->Events()->registerListener($event);

        $event = new Enlight_Event_EventHandler(
            'Enlight_Controller_Front_PreDispatch',
            array($this, 'onPreDispatch')
        );
        Shopware()->Events()->registerListener($event);
    }

    /**
     * Checks the url / the request and passes it around if necessary.
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onPreDispatch(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        if ($response->isException()
            || $request->isPost()
            || $request->isXmlHttpRequest()
            || $request->has('callback')
            || ($request->getModuleName() && $request->getModuleName() != 'frontend')
            || (!$request->getParam('rewriteAlias') && !$request->getParam('rewriteOld'))
            || !Shopware()->Session()->Bot && !$request->getCookie()
        ) {
            return;
        }

        $router = $args->getSubject()->Router();

        $query = $request->getQuery();
        $location = $router->assemble($query);
        $current =  $request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri();

        if ($location !== $current) {
            $response->setRedirect($location, 301);
        }
    }

    protected $shopId, $elementId;

    /**
     * @deprecated
     * This method was moved to the RebuildIndex plugin
     *
     * As the subscribed event might have been cached, the callback should not be removed right now
     */
    public function onAfterSendResponse(Enlight_Controller_EventArgs $args)
    {
        return;
    }

    /**
     * Reads the route based on the url.
     *
     * @param Enlight_Event_EventArgs $args
     * @return array|null
     */
    public function onRoute(Enlight_Event_EventArgs $args)
    {
        /** @var $request Enlight_Controller_Request_RequestHttp */
        $request = $args->getRequest();
        $url = $request->getPathInfo();
        if (strpos($url, '/backend') === 0 || strpos($url, '/api') === 0) {
            return;
        }
        if (!Shopware()->Bootstrap()->issetResource('Shop')) {
            return;
        }

        $shop = Shopware()->Shop();
        $sql = 'SELECT org_path, main FROM s_core_rewrite_urls WHERE path LIKE ? AND subshopID =?';
        $result = Shopware()->Db()->fetchRow($sql, array(
            ltrim($url, '/'),
            $shop->getId()
        ));
        if (!empty($result)) {
            $alias_list = $this->sGetQueryAliasList();
            foreach ($alias_list as $key => $alias) {
                $value = $request->getQuery($alias);
                if ($value !== null) {
                    $request->setQuery($key, $value);
                    $request->setQuery($alias, null);
                }
            }
            parse_str($result['org_path'], $query);
            if (empty($result['main'])) {
                $request->setParam('rewriteAlias', true);
            } else {
                $request->setParam('rewriteUrl', true);
            }
            return $query;
        }
        return null;
    }

    /**
     * Builds a url using the request.
     *
     * @param Enlight_Controller_Router_EventArgs $args
     * @return string
     */
    public function onAssemble(Enlight_Controller_Router_EventArgs $args)
    {
        $params = $args->getParams();

        if (!empty($params['module']) && $params['module'] != 'frontend') {
            return;
        }

        if (!Shopware()->Bootstrap()->issetResource('Db')
            || !Shopware()->Bootstrap()->issetResource('Shop')
        ) {
            return;
        }

        unset($params['sCoreId'], $params['sUseSSL'], $params['title'], $params['module']);
        if (!empty($params['sAction']) && $params['sAction'] == 'index') {
            unset($params['sAction']);
        }

        $id = md5(serialize($params));
        if (!isset($this->urls[$id])) {
            $this->urls[$id] = $this->assemble($params);
        }
        return $this->urls[$id];
    }

    /**
     * Build the url based on the query.
     *
     * @param array $query
     * @return string
     */
    public function assemble($query)
    {
        if (empty($query['sViewport'])) {
           // return null;
        }
        $orgQuery = array('sViewport' => $query['sViewport']);
        switch ($query['sViewport']) {
            case 'detail':
                $orgQuery['sArticle'] = $query['sArticle'];
                break;
            case 'blog':
                if(!empty($query['sAction'])) {
                    $orgQuery['sAction'] = $query['sAction'];
                    $orgQuery ['sCategory'] = $query['sCategory'];
                    $orgQuery['blogArticle'] = $query['blogArticle'];
                } else {
                    $orgQuery ['sCategory'] = $query['sCategory'];
                }
                break;
            case 'cat':
                $orgQuery ['sCategory'] = $query['sCategory'];
                break;
            case 'supplier':
                $orgQuery ['sSupplier'] = $query['sSupplier'];
                break;
            case 'campaign':
                $orgQuery ['sCategory'] = $query['sCategory'];
                $orgQuery ['emotionId'] = $query['emotionId'];
                break;
            case 'support':
            case 'ticket':
                if (!empty($query['sFid'])) {
                    $orgQuery['sFid'] = $query['sFid'];
                    if ($query['sFid'] == $this->inquiryId) {
                        $orgQuery['sInquiry'] = $query['sInquiry'];
                    }
                }
                break;
            case 'custom':
                $orgQuery['sCustom'] = $query['sCustom'];
                break;
            case 'content':
                $orgQuery['sContent'] = $query['sContent'];
                break;
            default:
                if (isset($query['sAction'])) {
                    $orgQuery['sAction'] = $query['sAction'];
                }
                break;
        }
        $orgPath = http_build_query($orgQuery, '', '&');

        $shopId = Shopware()->Shop()->getId();

        $sql = 'SELECT path FROM s_core_rewrite_urls WHERE org_path=? AND subshopID=? AND main=1 ORDER BY id DESC';
        $path = Shopware()->Db()->fetchOne($sql, array($orgPath, $shopId));
        if (empty($path)) {
            return null;
        }
        if ($this->urlToLower) {
            $path = strtolower($path);
        }
        $query = array_diff_key($query, $orgQuery);
        if (!empty($query)) {
            $path .= '?' . $this->sRewriteQuery($query);
        }
        return $path;
    }

    /**
     * The query alias list.
     *
     * @var array
     */
    protected $sQueryAliasList;

    /**
     * Returns the query alias list as an array.
     *
     * @return array
     */
    public function sGetQueryAliasList()
    {
        if (!isset($this->sQueryAliasList)) {
            $this->sQueryAliasList = array();
            if (!empty(Shopware()->Config()->SeoQueryAlias))
                foreach (explode(',', Shopware()->Config()->SeoQueryAlias) as $alias) {
                    list($key, $value) = explode('=', trim($alias));
                    $this->sQueryAliasList[$key] = $value;
                }
        }
        return $this->sQueryAliasList;
    }

    /**
     * Returns an alias of the list by name.
     *
     * @param string $key
     * @return string
     */
    public function sGetQueryAlias($key)
    {
        if (!isset($this->sQueryAliasList)) {
            $this->sGetQueryAliasList();
        }
        return isset($this->sQueryAliasList[$key]) ? $this->sQueryAliasList[$key] : null;
    }

    /**
     * Creates a url query based on the parameters.
     *
     * @param array $query
     * @return string
     */
    public function sRewriteQuery($query)
    {
        if (!empty($query)) {
            $tmp = array();
            foreach ($query as $key => $value) {
                if ($alias = $this->sGetQueryAlias($key)) {
                    $tmp[$alias] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            $query = $tmp;
            unset($tmp);
        }
        return http_build_query($query, '', '&');
    }
}
