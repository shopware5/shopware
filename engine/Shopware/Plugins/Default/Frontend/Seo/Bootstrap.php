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
 * @subpackage Seo
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware SEO Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_Seo_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install SEO-Plugin
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Plugins_ViewRenderer_FilterRender',
            'onFilterRender'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );
        return true;
    }

    /**
     * Returns capabilities so the plugin is default not installable and hidden in the plugin manager
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * Optimize Sourcecode / Apply seo rules
     *
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched() || $response->isException()
            || $request->getModuleName() != 'frontend'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $config = Shopware()->Config();

        $controllerBlacklist = preg_replace('#\s#', '', $config['sSEOVIEWPORTBLACKLIST']);
        $controllerBlacklist = explode(',', $controllerBlacklist);

        $queryBlacklist = preg_replace('#\s#', '', $config['sSEOQUERYBLACKLIST']);
        $queryBlacklist = explode(',', $queryBlacklist);

        if (!empty($config['sSEOMETADESCRIPTION'])) {
            if (!empty($view->sArticle['metaDescription'])) {
                $metaDescription = $view->sArticle['metaDescription'];
            } elseif (!empty($view->sArticle['description'])) {
                $metaDescription = $view->sArticle['description'];
            } elseif (!empty($view->sArticle['description_long'])) {
                $metaDescription = $view->sArticle['description_long'];
            } elseif (!empty($view->sCategoryContent['metaDescription'])) {
                $metaDescription = $view->sCategoryContent['metaDescription'];
            } elseif (!empty($view->sCategoryContent['cmstext'])) {
                $metaDescription = $view->sCategoryContent['cmstext'];
            }
            if (!empty($metaDescription)) {
                $metaDescription = html_entity_decode($metaDescription, ENT_COMPAT, 'UTF-8');
                $metaDescription = trim(preg_replace('/\s\s+/', ' ', strip_tags($metaDescription)));
                $metaDescription = htmlspecialchars($metaDescription);
            }
        }

        $controller = $request->getControllerName();

        if ($request->getQuery('sViewport') === 'supplier' || $request->getQuery('controller') === 'supplier') {
            $alias = $this->sGetQueryAliasList();

            if(array_key_exists('sSupplier', $alias) && ($index = array_search($alias['sSupplier'], $queryBlacklist, true))) {
                unset($queryBlacklist[$index]);
            }
            if($index = array_search('sSupplier', $queryBlacklist, true)) {
                unset($queryBlacklist[$index]);
            }

            $queryBlacklist[] = 'sCategory';
            if(array_key_exists('sCategory', $alias)) {
                $queryBlacklist[] = $alias['sCategory'];
            }
        }

        if (!empty($controllerBlacklist) && in_array($controller, $controllerBlacklist)) {
            $metaRobots = 'noindex,follow';
        } elseif (!empty($queryBlacklist)) {
            foreach ($queryBlacklist as $queryKey) {
                if ($request->getQuery($queryKey) !== null) {
                    $metaRobots = 'noindex,follow';
                    break;
                }
            }
        }

        $view->extendsTemplate('frontend/plugins/seo/index.tpl');

        if (!empty($metaRobots)) {
            $view->SeoMetaRobots = $metaRobots;
        }
        if (!empty($metaDescription)) {
            $view->SeoMetaDescription = $metaDescription;
        }
    }

    /**
     * Returns the query alias list as an array.
     *
     * @return array
     */
    public function sGetQueryAliasList()
    {
        $sQueryAliasList = array();
        if (!empty(Shopware()->Config()->SeoQueryAlias)) {
            foreach (explode(',', Shopware()->Config()->SeoQueryAlias) as $alias) {
                list($key, $value) = explode('=', trim($alias));
                $sQueryAliasList[$key] = $value;
            }
        }
        return $sQueryAliasList;
    }

    /**
     * Remove html-comments / whitespaces
     *
     * @param Enlight_Event_EventArgs $args
     * @return mixed|string
     */
    public function onFilterRender(Enlight_Event_EventArgs $args)
    {
        $source = $args->getReturn();

        if (strpos($source, '<html') === false) {
            return $source;
        }

        $config = Shopware()->Config();

        // Remove comments
        if (!empty($config['sSEOREMOVECOMMENTS'])) {
            $source = str_replace(array("\r\n", "\r"), "\n", $source);
            $expressions = array(
                // Remove comments
                '#(<(?:script|pre|textarea)[^>]*?>.*?</(?:script|pre|textarea)>)|(<style[^>]*?>.*?</style>)|(<!--\[.*?\]-->)|(<!--\s*\#\s*include virtual.*?-->)|<!--.*?-->#msiS' => '$1$2$3$4',
                // remove spaces between attributes (but not in attribute values!)
                '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
                // note: for some very weird reason trim() seems to remove spaces inside attributes.
                // maybe a \0 byte or something is interfering?
                '#^\s+#ms' => '',
                '#\s+$#ms' => '',
            );
            $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
        }

        return $source;
    }
}
