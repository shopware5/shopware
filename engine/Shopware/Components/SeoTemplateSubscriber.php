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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

class SeoTemplateSubscriber implements SubscriberInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(
        \Shopware_Components_Config $config,
        QueryAliasMapper $queryAliasMapper,
        ContextServiceInterface $contextService
    ) {
        $this->config = $config;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->contextService = $contextService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Plugins_ViewRenderer_FilterRender' => 'onFilterRender',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
        ];
    }

    /**
     * Optimize Sourcecode / Apply seo rules
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        $view = $controller->View();

        $controllerBlacklist = preg_replace('#\s#', '', $this->config->get('sSEOVIEWPORTBLACKLIST'));
        $controllerBlacklist = explode(',', $controllerBlacklist);

        $queryBlacklist = preg_replace('#\s#', '', $this->config->get('sSEOQUERYBLACKLIST'));
        $queryBlacklist = explode(',', $queryBlacklist);

        if (!empty($this->config->get('sSEOMETADESCRIPTION'))) {
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

        if (strtolower($request->getActionName()) === 'manufacturer' && strtolower($controller) === 'listing') {
            $alias = $this->queryAliasMapper->getQueryAliases();

            if (array_key_exists('sSupplier', $alias) && ($index = array_search($alias['sSupplier'], $queryBlacklist, true))) {
                unset($queryBlacklist[$index]);
            }

            if ($index = array_search('sSupplier', $queryBlacklist, true)) {
                unset($queryBlacklist[$index]);
            }

            $context = $this->contextService->getShopContext();

            if ($request->getQuery('sCategory') !== $context->getShop()->getCategory()->getId()) {
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
    }

    /**
     * Remove html-comments / whitespaces
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return mixed|string
     */
    public function onFilterRender(\Enlight_Event_EventArgs $args)
    {
        $source = $args->getReturn();

        if (strpos($source, '<html') === false) {
            return $source;
        }

        // Remove comments
        if (!empty($this->config->get('sSEOREMOVECOMMENTS'))) {
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
}
