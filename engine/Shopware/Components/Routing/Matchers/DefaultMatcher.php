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

namespace Shopware\Components\Routing\Matchers;

use Enlight_Controller_Dispatcher_Default as EnlightDispatcher;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\MatcherInterface;

class DefaultMatcher implements MatcherInterface
{
    /**
     * @var EnlightDispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @param string $separator
     */
    public function __construct(EnlightDispatcher $dispatcher, $separator = '/')
    {
        $this->dispatcher = $dispatcher;
        $this->separator = $separator;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context)
    {
        $path = trim($pathInfo, $this->separator);
        if (empty($path)) {
            return false;
        }

        $query = [];
        $params = [];

        foreach (explode($this->separator, $path) as $routePart) {
            $routePart = urldecode($routePart);
            if (empty($query[$context->getModuleKey()]) && $this->dispatcher->isValidModule($routePart)) {
                $query[$context->getModuleKey()] = $routePart;
            } elseif (empty($query[$context->getControllerKey()])) {
                $query[$context->getControllerKey()] = $routePart;
            } elseif (empty($query[$context->getActionKey()])) {
                $query[$context->getActionKey()] = $routePart;
            } else {
                $params[] = $routePart;
            }
        }

        if ($params) {
            $chunks = array_chunk($params, 2, false);
            foreach ($chunks as $chunk) {
                if (isset($chunk[1])) {
                    $query[$chunk[0]] = $chunk[1];
                } else {
                    $query[$chunk[0]] = '';
                }
            }
        }

        $query = $this->fillDefaults($context, $query);

        return $query;
    }

    /**
     * Fills up default values for module, controller and action
     *
     * @param array $query
     *
     * @return array
     */
    private function fillDefaults(Context $context, $query)
    {
        $defaults = [
            $context->getModuleKey() => $this->dispatcher->getDefaultModule(),
            $context->getControllerKey() => $this->dispatcher->getDefaultControllerName(),
            $context->getActionKey() => $this->dispatcher->getDefaultAction(),
        ];

        $query = array_merge($defaults, $query);

        return $query;
    }
}
