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

namespace Shopware\Components\Routing\GeneratorFilters;

use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\PreFilterInterface;

class DefaultPreFilter implements PreFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function preFilter($params, Context $context = null)
    {
        // Add support for "shopware.php?sViewport,cat&sCategory=3"
        if (is_string($params)) {
            $params = parse_url($params, PHP_URL_QUERY);
            $params = str_replace(',', '=', $params);
            parse_str($params, $params);
        }

        $globalParams = $context ? $context->getGlobalParams() : [];

        if (isset($params['sViewport'])) {
            $params['controller'] = $params['sViewport'];
        }
        if (isset($params['sAction'])) {
            $params['action'] = $params['sAction'];
        }
        unset($params['title'], $params['sViewport'], $params['sAction']);

        if (isset($params['controller']) || isset($params['module'])) {
            if (isset($params['module'])) {
                unset($globalParams['controller']);
            }
            unset($globalParams['controller'], $globalParams['action']);
        }

        /* @see \sArticles::buildNavigation */
        if (isset($params['sDetails'])) {
            $params['sArticle'] = $params['sDetails'];
            unset($params['sDetails']);
        }
        /* @see \Shopware_Controllers_Backend_Customer::performOrderAction */
        if (!isset($params['controller']) && isset($params['action']) && $params['action'] === 'performOrderRedirect') {
            $params['module'] = 'backend';
            $params['controller'] = 'customer';
        }
        /* @see \Shopware_Controllers_Widgets_Emotion */
        if (!isset($params['module']) && isset($globalParams['module']) && $globalParams['module'] === 'widgets') {
            $params['module'] = 'frontend';
        }
        $params = array_merge($globalParams, $params);
        if ($context) {
            $context->setParams($params);
        }

        return $params;
    }
}
