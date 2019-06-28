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

class FrontendPreFilter implements PreFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function preFilter($params, Context $context)
    {
        if (isset($params['sDetails'], $params['action']) && $params['action'] === 'detail') {
            $params['sArticle'] = $params['sDetails'];
            unset($params['sDetails']);
        }

        if (isset($params['action'])) {
            $params = array_merge(['action' => null], $params);
        }
        if (isset($params['controller'])) {
            $params = array_merge(['controller' => null], $params);
        }

        unset($params['sUseSSL'], $params['fullPath'], $params['forceSecure'],
            $params['sCoreId'], $params['rewriteOld'], $params['rewriteAlias'], $params['rewriteUrl']);

        if (isset($params['controller']) && $params['controller'] === 'detail' && $context->isRemoveCategory()) {
            unset($params['sCategory']);
        }

        return $params;
    }
}
