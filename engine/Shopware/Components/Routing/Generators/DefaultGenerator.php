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

namespace Shopware\Components\Routing\Generators;

use Enlight_Controller_Dispatcher_Default as EnlightDispatcher;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\GeneratorInterface;

/**
 * @see \Enlight_Controller_Router_Default
 */
class DefaultGenerator implements GeneratorInterface
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
    public function generate(array $params, Context $context)
    {
        $route = [];

        if (array_key_exists('_seo', $params)) {
            unset($params['_seo']);
        }

        $module = isset($params[$context->getModuleKey()])
            ? $params[$context->getModuleKey()]
            : $this->dispatcher->getDefaultModule();

        $controller = isset($params[$context->getControllerKey()])
            ? $params[$context->getControllerKey()]
            : $this->dispatcher->getDefaultControllerName();

        $action = isset($params[$context->getActionKey()])
            ? $params[$context->getActionKey()]
            : $this->dispatcher->getDefaultAction();

        unset($params[$context->getModuleKey()],
            $params[$context->getControllerKey()],
            $params[$context->getActionKey()]);

        if ($module !== $this->dispatcher->getDefaultModule()) {
            $route[] = $module;
        }

        $paramCount = \count($params);
        if ($paramCount > 0 || $controller !== $this->dispatcher->getDefaultControllerName() || $action !== $this->dispatcher->getDefaultAction()) {
            $route[] = $controller;
        }

        if ($paramCount > 0 || $action !== $this->dispatcher->getDefaultAction()) {
            $route[] = $action;
        }

        foreach ($params as $key => $value) {
            $route[] = $key;
            $route[] = $value;
        }

        $route = array_map('urlencode', $route);

        return implode($this->separator, $route);
    }
}
