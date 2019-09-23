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

use Shopware\Components\QueryAliasMapper;

/**
 * Shopware SEO Plugin
 */
class Shopware_Plugins_Frontend_Seo_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install SEO-Plugin
     *
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
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * Optimize Sourcecode / Apply SEO rules
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched() || $response->isException()
            || $request->getModuleName() !== 'frontend'
            || !$view->hasTemplate()
        ) {
            return;
        }

        $config = $this->get('config');

        /** @var QueryAliasMapper $mapper */
        $mapper = $this->get('query_alias_mapper');

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
                $metaDescription = htmlspecialchars($metaDescription, ENT_COMPAT);
            }
        }

        $controller = $request->getControllerName();

        if ($request->get('action') === 'manufacturer' && $request->get('controller') === 'listing') {
            $alias = $mapper->getQueryAliases();

            if (array_key_exists('sSupplier', $alias) && ($index = array_search($alias['sSupplier'], $queryBlacklist, true))) {
                unset($queryBlacklist[$index]);
            }

            if ($index = array_search('sSupplier', $queryBlacklist, true)) {
                unset($queryBlacklist[$index]);
            }

            if ($request->getQuery('sCategory') !== Shopware()->Shop()->getCategory()->getId()) {
                $queryBlacklist[] = 'sCategory';
                if (array_key_exists('sCategory', $alias)) {
                    $queryBlacklist[] = $alias['sCategory'];
                }
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
            $view->assign('SeoMetaRobots', $metaRobots);
        }
        if (!empty($metaDescription)) {
            $view->assign('SeoMetaDescription', $metaDescription);
        }

        if ($this->get('config')->get('hrefLangEnabled')) {
            $context = $this->get('shopware_storefront.context_service')->getShopContext();

            $params = $request->getParams();
            $sCategoryContent = $view->getAssign('sCategoryContent');

            if ($sCategoryContent && $request->getControllerName() === 'listing' && isset($sCategoryContent['canonicalParams']) && is_array($sCategoryContent['canonicalParams'])) {
                $params = $sCategoryContent['canonicalParams'];
            }

            $view->assign('sHrefLinks', $this->get('shopware_storefront.cached_href_lang_service')->getUrls($params, $context));
        }

        $view->assign('SeoDescriptionMaxLength', (int) $this->get('config')->get('metaDescriptionLength'));
    }

    /**
     * Remove html-comments / whitespaces
     *
     * @return mixed|string
     */
    public function onFilterRender(Enlight_Event_EventArgs $args)
    {
        $source = $args->getReturn();
        $config = $this->get('config');

        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject')->Action();

        if (!empty($config['minifyHtml']) && $this->canBeMinified($controller)) {
            $source = $this->get('shopware.components.template.html_minifier')->minify($source);
        }

        if (strpos($source, '<html') === false) {
            return $source;
        }

        // Remove comments
        if (!empty($config['sSEOREMOVECOMMENTS'])) {
            $source = str_replace(["\r\n", "\r"], "\n", $source);
            $expressions = [
                // Remove comments
                '#(<(?:script|pre|textarea)[^>]*?>.*?</(?:script|pre|textarea)>)|(<style[^>]*?>.*?</style>)|(<!--\[.*?\]-->)|(<!--\s*\#\s*include virtual.*?-->)|<!--.*?-->#msiS' => '$1$2$3$4',
                // remove spaces between attributes (but not in attribute values!)
                '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
                // note: for some very weird reason trim() seems to remove spaces inside attributes.
                // maybe a \0 byte or something is interfering?
                '#^\s+#ms' => '',
                '#\s+$#ms' => '',
            ];
            $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
        }

        return $source;
    }

    private function canBeMinified(Enlight_Controller_Action $controller): bool
    {
        /** @var Enlight_Event_EventManager $events */
        $events = $this->get('events');

        if ($event = $events->notifyUntil(__CLASS__ . '_canBeMinified', [
            'controller' => $controller,
            'subject' => $this,
        ])) {
            return $event->getReturn();
        }

        $contentType = $controller->Response()->headers->get('content-type', 'text/html');

        if (strpos($contentType, 'text/html') !== 0) {
            return false;
        }

        if (!$controller->View()->hasTemplate() || in_array(strtolower($controller->Request()->getModuleName()), ['backend', 'api'])) {
            return false;
        }

        $templateFile = $controller->View()->Template()->template_resource;

        if (substr($templateFile, -3) === '.js') {
            return false;
        }

        // The cart page can become very slow very fast due to many html tags for amount selections
        return $controller->Front()->Request()->getControllerName() !== 'checkout';
    }
}
